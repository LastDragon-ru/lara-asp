<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileDependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileTaskFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use SplFileInfo;
use Symfony\Component\Finder\Glob;

use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function dirname;
use function in_array;
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
     * @param array<array-key, string>|string|null                             $exclude glob(s) to exclude.
     * @param Closure(string $path, ?bool $result, float $duration): void|null $listener
     */
    public function run(string $path, array|string|null $exclude = null, ?Closure $listener = null): float {
        $start      = microtime(true);
        $extensions = array_map(static fn ($e) => "*.{$e}", array_keys($this->tasks));
        $processed  = [];
        $exclude    = array_map(Glob::toRegex(...), (array) $exclude);
        $root       = new Directory($path, true);

        try {
            $this->runIterator(
                $root->getFilesIterator(patterns: $extensions, exclude: $exclude),
                $root,
                $exclude,
                $listener ?? static fn () => null,
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
     * @param Iterator<array-key, File>                                   $iterator
     * @param array<array-key, string>                                    $exclude
     * @param Closure(string $path, ?bool $result, float $duration): void $listener
     * @param array<string, true>                                         $processed
     * @param array<string, File>                                         $stack
     */
    private function runIterator(
        Iterator $iterator,
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
     * @param Iterator<array-key, File>                                   $iterator
     * @param array<array-key, string>                                    $exclude
     * @param Closure(string $path, ?bool $result, float $duration): void $listener
     * @param array<string, true>                                         $processed
     * @param array<string, File>                                         $stack
     */
    private function runFile(
        Iterator $iterator,
        Directory $root,
        File $file,
        array $exclude,
        Closure $listener,
        array &$processed,
        array $stack,
    ): ?float {
        // Prepare
        $start   = microtime(true);
        $fileKey = $file->getPath();

        if (isset($processed[$fileKey])) {
            return null;
        }

        // Tasks?
        $tasks               = $this->tasks[$file->getExtension()] ?? [];
        $filePath            = $file->getRelativePath($root);
        $processed[$fileKey] = true;

        if (!$tasks) {
            $listener($filePath, null, microtime(true) - $start);

            return null;
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
                $listener($filePath, null, microtime(true) - $start);

                return null;
            }
        }

        // Process
        $paused          = 0;
        $directory       = dirname($file->getPath());
        $stack[$fileKey] = $file;

        try {
            foreach ($tasks as $task) {
                try {
                    // Run
                    $generator = $task($root, $file);

                    // Postponed?
                    if ($generator === null) {
                        $paused   += $this->runIterator($iterator, $root, $exclude, $listener, $processed, $stack);
                        $generator = $task($root, $file) ?? false;
                    }

                    // Dependencies?
                    $result = false;

                    if ($generator instanceof Generator) {
                        while ($generator->valid()) {
                            // Resolve
                            $path = $generator->current();
                            $path = match (true) {
                                $path instanceof SplFileInfo => $path->getPathname(),
                                $path instanceof File        => $path->getPath(),
                                default                      => $path,
                            };
                            $dependency    = $root->getFile(Path::getPath($directory, $path));
                            $dependencyKey = $dependency?->getPath();

                            if (!$dependency) {
                                throw new FileDependencyNotFound($root, $file, $path);
                            }

                            // Circular?
                            if (isset($stack[$dependencyKey])) {
                                throw new CircularDependency($root, $file, $dependency, array_values($stack));
                            }

                            // Processable?
                            if (!isset($processed[$dependencyKey]) && $root->isInside($dependency)) {
                                $paused += (float) $this->runFile(
                                    $iterator,
                                    $root,
                                    $dependency,
                                    $exclude,
                                    $listener,
                                    $processed,
                                    $stack,
                                );
                            }

                            // Continue
                            $generator->send($dependency);
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
        } catch (Exception $exception) {
            throw $exception;
        } finally {
            $duration = microtime(true) - $start - $paused;

            $listener($filePath, !isset($exception), $duration);
        }

        // Reset
        unset($stack[$fileKey]);

        // Return
        return $duration;
    }
}
