<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use Iterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileTaskFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessingFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use WeakMap;

use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
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
     * @param Closure(string $path, bool $result, float $duration): void|null $listener
     */
    public function run(string $path, ?Closure $listener = null): bool {
        $root = new Directory($path, true);

        try {
            foreach ($this->getIterator($root) as [$file, $dependencies]) {
                $tasks = $this->tasks[$file->getExtension()] ?? [];
                $start = microtime(true);

                try {
                    foreach ($tasks as $task) {
                        try {
                            if (!$task->run($root, $file, $dependencies[$task] ?? [])) {
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

                    if ($listener) {
                        $listener($file->getRelativePath($root), true, microtime(true) - $start);
                    }
                } catch (Exception $exception) {
                    if ($listener) {
                        $listener($file->getRelativePath($root), false, microtime(true) - $start);
                    }

                    throw $exception;
                }
            }
        } catch (ProcessorError $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new ProcessingFailed($root, $exception);
        }

        return true;
    }

    /**
     * @return Iterator<array-key, array{File, WeakMap<Task, array<string, File>>}>
     */
    protected function getIterator(Directory $root): Iterator {
        $extensions = array_map(static fn ($e) => "*.{$e}", array_keys($this->tasks));
        $processed  = [];

        foreach ($root->getFilesIterator($extensions) as $item) {
            foreach ($this->getFileIterator($root, $item, [$item->getPath() => $item]) as [$file, $dependencies]) {
                if (isset($processed[$file->getPath()])) {
                    continue;
                }

                $processed[$file->getPath()] = true;

                yield [$file, $dependencies];
            }
        }
    }

    /**
     * @param array<string, File> $stack
     *
     * @return Iterator<array-key, array{File, WeakMap<Task, array<string, File>>}>
     */
    private function getFileIterator(Directory $root, File $file, array $stack): Iterator {
        // Prepare
        $directory = $root->getDirectory($file);

        if (!$directory) {
            return;
        }

        // Dependencies
        /** @var WeakMap<Task, array<string, File>> $dependencies */
        $dependencies = new WeakMap();
        $tasks        = $this->tasks[$file->getExtension()] ?? [];
        $map          = [];

        foreach ($tasks as $task) {
            $taskDependencies = [];
            $deps             = $task->getDependencies($directory, $file);

            foreach ($deps as $path) {
                // File?
                $dependency = $map[$path] ?? $directory->getFile($path);
                $dependency = $map[$dependency?->getPath()] ?? $dependency;
                $key        = $dependency?->getPath();

                if ($dependency === null) {
                    continue;
                }

                // Circular?
                if (isset($stack[$key])) {
                    throw new CircularDependency($root, $dependency, array_values($stack));
                }

                // Save
                $map[$key]               = $dependency;
                $map[$path]              = $dependency;
                $stack[$key]             = $dependency;
                $taskDependencies[$path] = $dependency;

                // Tasks?
                if (!isset($this->tasks[$dependency->getExtension()])) {
                    continue;
                }

                // Yield
                yield from $this->getFileIterator($root, $dependency, $stack);

                unset($stack[$key]);
            }

            $dependencies[$task] = $taskDependencies;
        }

        // File
        yield [$file, $dependencies];
    }
}
