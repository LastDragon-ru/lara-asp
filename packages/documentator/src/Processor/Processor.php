<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileTaskFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Symfony\Component\Finder\Glob;
use Throwable;

use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function in_array;
use function is_iterable;
use function microtime;
use function preg_match;

class Processor {
    /**
     * @var array<string, list<Task>>
     */
    private array $tasks = [];

    public function __construct() {
        // empty
    }

    public function task(Task $task): static {
        foreach (array_unique($task->getExtensions()) as $ext) {
            if (!isset($this->tasks[$ext])) {
                $this->tasks[$ext] = [];
            }

            if (!in_array($task, $this->tasks[$ext], true)) {
                $this->tasks[$ext][] = $task;
            }
        }

        return $this;
    }

    /**
     * @param array<array-key, string>|string|null                              $exclude glob(s) to exclude.
     * @param Closure(string $path, Result $result, float $duration): void|null $listener
     */
    public function run(string $path, array|string|null $exclude = null, ?Closure $listener = null): float {
        $start      = microtime(true);
        $extensions = array_map(static fn ($e) => "*.{$e}", array_keys($this->tasks));
        $processed  = [];
        $exclude    = array_map(Glob::toRegex(...), (array) $exclude);
        $root       = new Directory($path, true);
        $fs         = new FileSystem();

        try {
            $this->runIterator(
                $fs->getFilesIterator($root, patterns: $extensions, exclude: $exclude),
                $fs,
                $root,
                $exclude,
                static function (string $path, Result $result, float $duration) use ($listener): float {
                    $start = microtime(true);

                    if ($listener) {
                        $listener($path, $result, $duration);
                    }

                    return microtime(true) - $start;
                },
                $processed,
                [],
            );
        } catch (ProcessorError $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new ProcessingFailed($root, $exception);
        }

        return microtime(true) - $start;
    }

    /**
     * @param Iterator<array-key, File>             $iterator
     * @param array<array-key, string>              $exclude
     * @param Closure(string, Result, float): float $listener
     * @param array<string, true>                   $processed
     * @param array<string, File>                   $stack
     */
    private function runIterator(
        Iterator $iterator,
        FileSystem $fs,
        Directory $root,
        array $exclude,
        Closure $listener,
        array &$processed,
        array $stack,
    ): float {
        $time = 0;

        while ($iterator->valid()) {
            $file = $iterator->current();

            $iterator->next();

            $time += (float) $this->runFile(
                $iterator,
                $fs,
                $root,
                $file,
                $exclude,
                $listener,
                $processed,
                $stack,
            );
        }

        return $time;
    }

    /**
     * @param Iterator<array-key, File>             $iterator
     * @param array<array-key, string>              $exclude
     * @param Closure(string, Result, float): float $listener
     * @param array<string, true>                   $processed
     * @param array<string, File>                   $stack
     */
    private function runFile(
        Iterator $iterator,
        FileSystem $fs,
        Directory $root,
        File $file,
        array $exclude,
        Closure $listener,
        array &$processed,
        array $stack,
    ): ?float {
        // Processed?
        $fileKey = $file->getPath();

        if (isset($processed[$fileKey])) {
            return null;
        }

        // Circular?
        if (isset($stack[$fileKey])) {
            throw new CircularDependency($root, $file, $file, array_values($stack));
        }

        // Outside?
        $start    = microtime(true);
        $filePath = $file->getRelativePath($root);

        if (!$root->isInside($file)) {
            return $listener($filePath, Result::Skipped, microtime(true) - $start);
        }

        // Tasks?
        $tasks = $this->tasks[$file->getExtension()] ?? [];

        if (!$tasks) {
            return $listener($filePath, Result::Skipped, microtime(true) - $start);
        }

        // Excluded?
        if ($exclude) {
            $excluded = false;

            foreach ($exclude as $regexp) {
                if (preg_match($regexp, $filePath)) {
                    $excluded = true;
                    break;
                }
            }

            if ($excluded) {
                return $listener($filePath, Result::Skipped, microtime(true) - $start);
            }
        }

        // Process
        $paused          = 0;
        $stack[$fileKey] = $file;

        try {
            foreach ($tasks as $task) {
                try {
                    // Run
                    $generator = $task($root, $file);

                    // Postponed?
                    if ($generator === null) {
                        $paused   += $this->runIterator(
                            $iterator,
                            $fs,
                            $root,
                            $exclude,
                            $listener,
                            $processed,
                            $stack,
                        );
                        $generator = $task($root, $file) ?? false;
                    }

                    // Dependencies?
                    $result = false;

                    if ($generator instanceof Generator) {
                        while ($generator->valid()) {
                            $dependencies = ($generator->current())($fs, $root, $file);
                            $dependencies = is_iterable($dependencies) ? $dependencies : [$dependencies];

                            foreach ($dependencies as $dependency) {
                                if ($dependency instanceof File) {
                                    $paused += (float) $this->runFile(
                                        $iterator,
                                        $fs,
                                        $root,
                                        $dependency,
                                        $exclude,
                                        $listener,
                                        $processed,
                                        $stack,
                                    );
                                } elseif ($dependency === null) {
                                    $listener($filePath, Result::Missed, 0);
                                } else {
                                    // empty
                                }

                                $generator->send($dependency);
                            }
                        }

                        $result = $generator->getReturn();
                    } else {
                        $result = $generator;
                    }

                    if ($result !== true) {
                        throw new FileTaskFailed($root, $file, $task);
                    }
                } catch (ProcessorError $exception) {
                    throw $exception;
                } catch (Exception $exception) {
                    throw new FileTaskFailed($root, $file, $task, $exception);
                }
            }

            if (!$file->save()) {
                throw new FileSaveFailed($root, $file);
            }
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            $result              = !isset($exception) ? Result::Success : Result::Failed;
            $duration            = microtime(true) - $start - $paused;
            $duration           -= $listener($filePath, $result, $duration);
            $processed[$fileKey] = true;
        }

        // Reset
        unset($stack[$fileKey]);

        // Return
        return $duration;
    }
}
