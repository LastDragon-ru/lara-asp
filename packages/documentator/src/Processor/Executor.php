<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\ProcessorError;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\TaskFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\TaskNotInvokable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Globs;

use function array_values;
use function end;

/**
 * @internal
 */
class Executor {
    private ExecutorState     $state;
    private readonly Resolver $resolver;

    /**
     * @var array<string, true>
     */
    private array $processed = [];

    /**
     * @var array<string, File>
     */
    private array $stack = [];

    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly Tasks $tasks,
        private readonly FileSystem $fs,
        private readonly Iterator $iterator,
        private readonly Globs $exclude,
    ) {
        $this->state    = ExecutorState::Created;
        $this->resolver = new Resolver($this->dispatcher, $this->fs, $this->onResolve(...), $this->onQueue(...));
    }

    public function run(): void {
        $file        = null;
        $this->state = ExecutorState::Preparation;

        foreach ($this->iterator as $item) {
            if ($file === null) {
                $this->hook(Hook::BeforeProcessing, $item);

                $this->state = ExecutorState::Iteration;
            }

            $this->file($item);

            $file = $item;
        }

        if ($file !== null) {
            $this->state = ExecutorState::Finished;

            $this->hook(Hook::AfterProcessing, $file);
        }
    }

    protected function hook(Hook $hook, File $file): void {
        // Tasks?
        if ($hook === Hook::File || !$this->tasks->has($hook)) {
            return;
        }

        // Run
        $this->dispatcher->notify(new HookStarted($hook, $this->fs->getPathname($file)));

        try {
            $this->tasks($this->tasks->get($hook), $hook, $file);
        } catch (Exception $exception) {
            $this->dispatcher->notify(new HookFinished(HookFinishedResult::Failed));

            throw $exception;
        }

        $this->dispatcher->notify(new HookFinished(HookFinishedResult::Success));
    }

    protected function file(File $file): void {
        // Processed?
        $path = (string) $file;

        if (isset($this->processed[$path])) {
            return;
        }

        // Circular?
        if (isset($this->stack[$path])) {
            // The $file cannot be processed if it is skipped, so we can return
            // it safely in this case.
            if (!$this->isSkipped($file)) {
                $this->dispatcher->notify(new FileStarted($this->fs->getPathname($file)));
                $this->dispatcher->notify(new FileFinished(FileFinishedResult::Failed));

                throw new DependencyCircularDependency($file, array_values($this->stack));
            } else {
                return;
            }
        }

        // Event
        $this->dispatcher->notify(new FileStarted($this->fs->getPathname($file)));

        // Skipped?
        if ($this->isSkipped($file)) {
            $this->dispatcher->notify(new FileFinished(FileFinishedResult::Skipped));

            $this->processed[$path] = true;

            return;
        }

        // Process
        $this->stack[$path] = $file;

        try {
            $this->tasks($this->tasks->get($file), Hook::File, $file);
        } catch (Exception $exception) {
            $this->dispatcher->notify(new FileFinished(FileFinishedResult::Failed));

            throw $exception;
        } finally {
            $this->processed[$path] = true;
        }

        // Event
        $this->dispatcher->notify(new FileFinished(FileFinishedResult::Success));

        // Reset
        unset($this->stack[$path]);
    }

    /**
     * @param iterable<int, Task> $tasks
     */
    protected function tasks(iterable $tasks, Hook $hook, File $file): void {
        $this->fs->begin();

        foreach ($tasks as $task) {
            $this->task($task, $hook, $file);

            $this->resolver->check();
        }

        $this->fs->commit();
    }

    protected function task(Task $task, Hook $hook, File $file): void {
        $this->dispatcher->notify(new TaskStarted($task::class));

        try {
            try {
                if ($task instanceof FileTask) {
                    $task($this->resolver, $file);
                } elseif ($task instanceof HookTask) {
                    $task($this->resolver, $file, $hook);
                } else {
                    throw new TaskNotInvokable($task, $hook, $file);
                }

                $this->resolver->check();
            } catch (ProcessorError $exception) {
                throw $exception;
            } catch (Exception $exception) {
                throw new TaskFailed($task, $hook, $file, $exception);
            }

            $this->dispatcher->notify(new TaskFinished(TaskFinishedResult::Success));
        } catch (Exception $exception) {
            $this->dispatcher->notify(new TaskFinished(TaskFinishedResult::Failed));

            throw $exception;
        }
    }

    protected function queue(File $file): void {
        $this->iterator->push($file->getPath());
    }

    protected function onResolve(File $resolved): void {
        // Possible?
        if ($this->state->is(ExecutorState::Created)) {
            throw new DependencyUnavailable();
        }

        // Skipped?
        if ($this->isSkipped($resolved)) {
            return;
        }

        // Process
        if (!$this->state->is(ExecutorState::Preparation) && end($this->stack) !== $resolved) {
            $this->file($resolved);
        }
    }

    protected function onQueue(File $resolved): void {
        // Possible?
        if ($this->state->is(ExecutorState::Finished)) {
            throw new DependencyUnavailable();
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
        if (!$this->fs->input->isInside($file->getPath())) {
            return true;
        }

        // Excluded?
        $path     = $this->fs->input->getRelativePath($file->getPath());
        $excluded = $this->exclude->isMatch($path);

        if ($excluded) {
            return true;
        }

        // Return
        return false;
    }
}
