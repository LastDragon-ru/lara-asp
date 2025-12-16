<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Exception;
use LastDragon_ru\GlobMatcher\Contracts\Matcher;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\TaskFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\TaskNotInvokable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Hook;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Tasks;
use LastDragon_ru\Path\FilePath;

use function array_last;
use function array_values;

/**
 * @internal
 */
class Executor {
    private State             $state;
    private readonly Iterator $iterator;
    private readonly Resolver $resolver;

    /**
     * @var array<string, true>
     */
    private array $processed = [];

    /**
     * @var array<string, FilePath>
     */
    private array $stack = [];

    /**
     * @param iterable<mixed, FilePath> $files
     */
    public function __construct(
        private readonly ContainerResolver $container,
        private readonly Dispatcher $dispatcher,
        private readonly Tasks $tasks,
        private readonly FileSystem $fs,
        iterable $files,
        private readonly Matcher $skipped,
    ) {
        $this->state    = State::Created;
        $this->iterator = new Iterator($this->fs, $files);
        $this->resolver = new Resolver(
            $this->container,
            $this->dispatcher,
            $this->fs,
            $this->onResolve(...),
            $this->onQueue(...),
        );
    }

    public function run(): void {
        $file        = null;
        $this->state = State::Preparation;

        foreach ($this->iterator as $item) {
            if ($file === null) {
                $this->hook(Hook::BeforeProcessing, $item);

                $this->state = State::Iteration;
            }

            $this->file($item);

            $file = $item;
        }

        if ($file !== null) {
            $this->state = State::Finished;

            $this->hook(Hook::AfterProcessing, $file);
        }
    }

    protected function hook(Hook $hook, File $file): void {
        // Tasks?
        if ($hook === Hook::File || !$this->tasks->has($hook)) {
            return;
        }

        // Run
        $result = ($this->dispatcher)(new HookBegin($hook, $file->path), HookResult::Success);

        try {
            $this->tasks($this->tasks->get($hook), $hook, $file);
        } catch (Exception $exception) {
            $result = HookResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new HookEnd($result));
        }
    }

    protected function file(File $file): void {
        // Processed?
        $path = (string) $file->path;

        if (isset($this->processed[$path])) {
            return;
        }

        // Circular?
        if (isset($this->stack[$path])) {
            // The $file cannot be processed if it is skipped, so we can return
            // it safely in this case.
            if (!$this->isSkipped($file)) {
                ($this->dispatcher)(new FileBegin($file->path));
                ($this->dispatcher)(new FileEnd(FileResult::Error));

                throw new DependencyCircularDependency($file->path, array_values($this->stack));
            } else {
                return;
            }
        }

        // Process
        $result             = ($this->dispatcher)(new FileBegin($file->path), FileResult::Success);
        $this->stack[$path] = $file->path;

        try {
            if (!$this->isSkipped($file)) {
                $this->tasks($this->tasks->get($file), Hook::File, $file);
            } else {
                $result = FileResult::Skipped;
            }
        } catch (Exception $exception) {
            $result = FileResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new FileEnd($result));

            $this->processed[$path] = true;

            unset($this->stack[$path]);
        }
    }

    /**
     * @param iterable<int, Task> $tasks
     */
    protected function tasks(iterable $tasks, Hook $hook, File $file): void {
        $this->fs->begin($file->path->directory());

        foreach ($tasks as $task) {
            $this->task($task, $hook, $file);
        }

        $this->fs->commit();
    }

    protected function task(Task $task, Hook $hook, File $file): void {
        $result = ($this->dispatcher)(new TaskBegin($task::class), TaskResult::Success);

        try {
            try {
                if ($task instanceof FileTask) {
                    $task($this->resolver, $file);
                } elseif ($task instanceof HookTask) {
                    $task($this->resolver, $file, $hook);
                } else {
                    throw new TaskNotInvokable($task, $hook, $file->path);
                }
            } catch (ProcessorError $exception) {
                throw $exception;
            } catch (Exception $exception) {
                throw new TaskFailed($task, $hook, $file->path, $exception);
            }
        } catch (Exception $exception) {
            $result = TaskResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new TaskEnd($result));
        }
    }

    protected function queue(File $file): void {
        $this->iterator->push($file);
    }

    protected function onResolve(File $resolved): void {
        // Possible?
        if ($this->state->is(State::Created)) {
            throw new DependencyUnavailable($resolved->path);
        }

        // Skipped?
        if ($this->isSkipped($resolved)) {
            return;
        }

        // Process
        if (!$this->state->is(State::Preparation) && !$resolved->path->equals(array_last($this->stack))) {
            $this->file($resolved);
        }
    }

    protected function onQueue(File $resolved): void {
        // Possible?
        if ($this->state->is(State::Finished)) {
            throw new DependencyUnavailable($resolved->path);
        }

        // Skipped?
        if ($this->isSkipped($resolved)) {
            return;
        }

        // Queue
        $this->queue($resolved);
    }

    protected function isSkipped(File $file): bool {
        // Tasks?
        if (!$this->tasks->has($file)) {
            return true;
        }

        // Outside?
        if (!$this->fs->input->contains($file->path)) {
            return true;
        }

        // Excluded?
        $path    = $this->fs->input->relative($file->path);
        $skipped = $path === null || $this->skipped->match($path);

        if ($skipped) {
            return true;
        }

        // Return
        return false;
    }
}
