<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use Generator;
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

use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function dirname;
use function in_array;
use function microtime;

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
     * @param Closure(string $path, ?bool $result, float $duration): void|null $listener
     */
    public function run(string $path, ?Closure $listener = null): void {
        $extensions = array_map(static fn ($e) => "*.{$e}", array_keys($this->tasks));
        $processed  = [];
        $root       = new Directory($path, true);

        try {
            foreach ($root->getFilesIterator($extensions) as $file) {
                $this->runFile($root, $file, $listener, $processed, [], []);
            }
        } catch (ProcessorError $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new ProcessingFailed($root, $exception);
        }
    }

    /**
     * @param Closure(string $path, ?bool $result, float $duration): void|null $listener
     * @param array<string, true>                                              $processed
     * @param array<string, File>                                              $resolved
     * @param array<string, File>                                              $stack
     */
    private function runFile(
        Directory $root,
        File $file,
        ?Closure $listener,
        array &$processed,
        array $resolved,
        array $stack,
    ): ?float {
        // Prepare
        $fileKey = $file->getPath();

        if (isset($processed[$fileKey])) {
            return null;
        }

        // Tasks?
        $tasks               = $this->tasks[$file->getExtension()] ?? [];
        $processed[$fileKey] = true;

        if (!$tasks) {
            if ($listener) {
                $listener($file->getRelativePath($root), null, 0);
            }

            return null;
        }

        // Process
        $start           = microtime(true);
        $paused          = 0;
        $directory       = dirname($file->getPath());
        $stack[$fileKey] = $file;

        try {
            foreach ($tasks as $task) {
                try {
                    $result    = false;
                    $generator = $task($root, $file);

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

                            // Resolved?
                            $dependency               = $resolved[$dependencyKey] ?? $dependency;
                            $resolved[$dependencyKey] = $dependency;

                            // Processable?
                            if (!isset($processed[$dependencyKey]) && $root->isInside($dependency)) {
                                $paused += (float) $this->runFile(
                                    $root,
                                    $dependency,
                                    $listener,
                                    $processed,
                                    $resolved,
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

            if ($listener) {
                $listener($file->getRelativePath($root), !isset($exception), $duration);
            }
        }

        // Reset
        unset($stack[$fileKey]);

        // Return
        return $duration;
    }
}
