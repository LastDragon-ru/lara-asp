<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataError;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileTaskFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Throwable;

use function array_merge;
use function array_values;
use function microtime;
use function preg_match;

/**
 * @internal
 */
class Executor {
    /**
     * @var array<string, true>
     */
    private array $processed = [];

    /**
     * @var array<string, File>
     */
    private array $stack = [];

    public function __construct(
        private readonly FileSystem $fs,
        private readonly Directory $root,
        /**
         * @var array<array-key, string>
         */
        private readonly array $exclude,
        /**
         * @var array<string, list<Task>>
         */
        private readonly array $tasks,
        /**
         * @var Iterator<array-key, File>
         */
        private readonly Iterator $iterator,
        /**
         * @var Closure(string, Result, float): void|null
         */
        private readonly ?Closure $listener = null,
    ) {
        // empty
    }

    public function run(): void {
        $this->runIterator();
    }

    private function runIterator(): float {
        $time = 0;

        while ($this->iterator->valid()) {
            $file = $this->iterator->current();

            $this->iterator->next();

            $time += $this->runFile($file);
        }

        return $time;
    }

    private function runFile(File $file): float {
        // Processed?
        $path = $file->getPath();

        if (isset($this->processed[$path])) {
            return 0;
        }

        // Circular?
        if (isset($this->stack[$path])) {
            throw new CircularDependency($this->root, $file, $file, array_values($this->stack));
        }

        // Skipped?
        if ($this->isSkipped($file)) {
            return $this->dispatch($file, Result::Skipped, 0);
        }

        // Process
        $tasks              = array_merge($this->tasks[$file->getExtension()] ?? [], $this->tasks['*'] ?? []);
        $start              = microtime(true);
        $paused             = 0;
        $this->stack[$path] = $file;

        try {
            foreach ($tasks as $task) {
                try {
                    // Run
                    $generator = $task($this->root, $file);

                    // Postponed?
                    if ($generator === null) {
                        $paused   += $this->runIterator();
                        $generator = $task($this->root, $file) ?? false;
                    }

                    // Dependencies?
                    $result = false;

                    if ($generator instanceof Generator) {
                        while ($generator->valid()) {
                            $dependency = $generator->current();
                            $resolved   = $dependency($this->fs, $this->root, $file);

                            if ($resolved instanceof Iterator) {
                                $resolved = new ExecutorIterator($dependency, $resolved, $this->runDependency(...));
                            } else {
                                $paused += $this->runDependency($dependency, $resolved);
                            }

                            $generator->send($resolved);

                            if ($resolved instanceof ExecutorIterator) {
                                $paused += $resolved->getDuration();
                            }
                        }

                        $result = $generator->getReturn();
                    } else {
                        $result = $generator;
                    }

                    if ($result !== true) {
                        throw new FileTaskFailed($this->root, $file, $task);
                    }
                } catch (FileMetadataError $exception) {
                    throw new FileMetadataFailed(
                        $this->root,
                        $exception->getTarget(),
                        $exception->getMetadata(),
                        $exception->getPrevious(),
                    );
                } catch (ProcessorError $exception) {
                    throw $exception;
                } catch (Exception $exception) {
                    throw new FileTaskFailed($this->root, $file, $task, $exception);
                }
            }

            if (!$this->fs->save($file)) {
                throw new FileSaveFailed($this->root, $file);
            }
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            $result                 = isset($exception) ? Result::Failed : Result::Success;
            $duration               = microtime(true) - $start - $paused;
            $duration              -= $this->dispatch($file, $result, $duration);
            $this->processed[$path] = true;
        }

        // Reset
        unset($this->stack[$path]);

        // Return
        return $duration;
    }

    /**
     * @param Dependency<*> $dependency
     */
    private function runDependency(Dependency $dependency, mixed $resolved): float {
        $duration = 0;

        if ($resolved instanceof File) {
            $duration += $this->runFile($resolved);
        } elseif ($resolved === null) {
            $duration += $this->dispatch($dependency, Result::Missed, 0);
        } else {
            // empty
        }

        return $duration;
    }

    /**
     * @param Dependency<*>|File $file
     */
    private function dispatch(Dependency|File $file, Result $result, float $duration): float {
        // Listener?
        if ($this->listener === null) {
            return 0;
        }

        // Call
        $start = microtime(true);
        $path  = match (true) {
            $file instanceof Dependency => Path::getRelativePath($this->root->getPath(), (string) $file),
            default                     => $file->getRelativePath($this->root),
        };

        ($this->listener)($path, $result, $duration);

        return microtime(true) - $start;
    }

    private function isSkipped(File $file): bool {
        // Tasks?
        if (!isset($this->tasks['*'])) {
            $tasks = $this->tasks[$file->getExtension()] ?? [];

            if (!$tasks) {
                return true;
            }
        }

        // Outside?
        $path = $file->getRelativePath($this->root);

        if (!$this->root->isInside($file)) {
            return true;
        }

        // Excluded?
        $excluded = false;

        foreach ($this->exclude as $regexp) {
            if (preg_match($regexp, $path)) {
                $excluded = true;
                break;
            }
        }

        if ($excluded) {
            return true;
        }

        // Return
        return false;
    }
}
