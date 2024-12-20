<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use Generator;
use Iterator;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\TaskFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Throwable;
use Traversable;

use function array_values;
use function microtime;

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
        /**
         * @var array<array-key, string>
         */
        private readonly array $exclude,
        /**
         * @var InstanceList<Task>
         */
        private readonly InstanceList $tasks,
        /**
         * @var Iterator<array-key, File>
         */
        private readonly Iterator $iterator,
        /**
         * @var Closure(FilePath, Result, float): void|null
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
        $path = (string) $file;

        if (isset($this->processed[$path])) {
            return 0;
        }

        // Circular?
        if (isset($this->stack[$path])) {
            throw new DependencyCircularDependency($this->fs, $file, array_values($this->stack));
        }

        // Skipped?
        if ($this->isSkipped($file)) {
            return $this->dispatch($file, Result::Skipped, 0);
        }

        // Process
        $tasks              = $this->tasks->get($file->getExtension(), '*');
        $start              = microtime(true);
        $paused             = 0;
        $this->stack[$path] = $file;

        try {
            foreach ($tasks as $task) {
                try {
                    // Run
                    $result    = false;
                    $generator = $task($file);

                    // Dependencies?
                    if ($generator instanceof Generator) {
                        while ($generator->valid()) {
                            $dependency = $generator->current();
                            $resolved   = $dependency($this->fs);

                            if ($resolved instanceof Traversable) {
                                $resolved = new ExecutorTraversable($dependency, $resolved, $this->runDependency(...));
                            } else {
                                $paused += $this->runDependency($dependency, $resolved);
                            }

                            $generator->send($resolved);

                            if ($resolved instanceof ExecutorTraversable) {
                                $paused += $resolved->getDuration();
                            }
                        }

                        $result = $generator->getReturn();
                    } else {
                        $result = $generator;
                    }

                    if ($result !== true) {
                        throw new TaskFailed($this->fs, $file, $task);
                    }
                } catch (FileMetadataUnresolvable $exception) {
                    throw new MetadataUnresolvable(
                        $this->fs,
                        $exception->getTarget(),
                        $exception->getMetadata(),
                        $exception->getPrevious(),
                    );
                } catch (ProcessorError $exception) {
                    throw $exception;
                } catch (Exception $exception) {
                    throw new TaskFailed($this->fs, $file, $task, $exception);
                }
            }

            if (!$this->fs->save($file)) {
                throw new FileSaveFailed($this->fs, $file);
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
            $file instanceof Dependency => new FilePath((string) $file),
            default                     => $file->getPath(),
        };
        $path = $this->fs->input->getRelativePath($path);

        ($this->listener)($path, $result, $duration);

        return microtime(true) - $start;
    }

    private function isSkipped(File $file): bool {
        // Tasks?
        if (!$this->tasks->has($file->getExtension(), '*')) {
            return true;
        }

        // Outside?
        if (!$this->fs->input->isInside($file->getPath())) {
            return true;
        }

        // Excluded?
        $path     = $this->fs->input->getRelativePath($file->getPath());
        $excluded = false;

        foreach ($this->exclude as $regexp) {
            if ($path->isMatch($regexp)) {
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
