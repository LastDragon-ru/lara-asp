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
    public function run(string $path, ?Closure $listener = null): void {
        $root = new Directory($path, true);

        try {
            foreach ($this->getIterator($root) as [$directory, $file, $dependencies]) {
                $tasks = $this->tasks[$file->getExtension()] ?? [];
                $start = microtime(true);

                try {
                    foreach ($tasks as $task) {
                        try {
                            if (!$task->run($root, $directory, $file, $dependencies[$task] ?? [])) {
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
    }

    /**
     * @return Iterator<array-key, array{Directory, File, WeakMap<Task, array<array-key, ?File>>}>
     */
    protected function getIterator(Directory $root): Iterator {
        $extensions = array_map(static fn ($e) => "*.{$e}", array_keys($this->tasks));
        $processed  = [];

        foreach ($root->getFilesIterator($extensions) as $item) {
            $files = $this->getFileIterator($root, $item, [$item->getPath() => $item]);

            foreach ($files as [$directory, $file, $dependencies]) {
                if (isset($processed[$file->getPath()])) {
                    continue;
                }

                $processed[$file->getPath()] = true;

                yield [$directory, $file, $dependencies];
            }
        }
    }

    /**
     * @param array<string, File> $stack
     * @param array<string, File> $resolved
     *
     * @return Iterator<array-key, array{Directory, File, WeakMap<Task, array<array-key, ?File>>}>
     */
    private function getFileIterator(Directory $root, File $file, array $stack, array $resolved = []): Iterator {
        // Prepare
        $directory = $root->getDirectory($file);

        if (!$directory) {
            return;
        }

        // Dependencies
        /** @var WeakMap<Task, array<array-key, ?File>> $fileDependencies */
        $fileDependencies = new WeakMap();
        $processed        = [];
        $tasks            = $this->tasks[$file->getExtension()] ?? [];
        $map              = [];

        foreach ($tasks as $task) {
            $taskDependencies = [];
            $dependencies     = $task->getDependencies($root, $directory, $file);

            foreach ($dependencies as $key => $dependency) {
                // File?
                $taskDependency         = $map[$dependency] ?? $directory->getFile($dependency);
                $dependencyKey          = $taskDependency?->getPath();
                $taskDependency         = $resolved[$dependencyKey] ?? $map[$dependencyKey] ?? $taskDependency;
                $taskDependencies[$key] = $taskDependency;

                if ($taskDependency === null || isset($processed[$dependencyKey])) {
                    continue;
                }

                // Circular?
                if (isset($stack[$dependencyKey])) {
                    throw new CircularDependency($root, $taskDependency, array_values($stack));
                }

                // Save
                $map[$dependency]          = $taskDependency;
                $map[$dependencyKey]       = $taskDependency;
                $stack[$dependencyKey]     = $taskDependency;
                $resolved[$dependencyKey]  = $taskDependency;
                $processed[$dependencyKey] = true;

                // Tasks?
                if (!isset($this->tasks[$taskDependency->getExtension()])) {
                    continue;
                }

                // Inside?
                if (!$root->isInside($taskDependency)) {
                    continue;
                }

                // Yield
                yield from $this->getFileIterator($root, $taskDependency, $stack, $resolved);

                unset($stack[$dependencyKey]);
            }

            $fileDependencies[$task] = $taskDependencies;
        }

        // File
        yield [$directory, $file, $fileDependencies];
    }
}
