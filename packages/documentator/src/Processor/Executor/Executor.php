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
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\TaskNotInvokable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Hook;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks;
use LastDragon_ru\Path\DirectoryPath;
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
     * @param iterable<mixed, DirectoryPath|FilePath> $files
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
        $this->iterator = new Iterator($files);
        $this->resolver = new Resolver(
            $this->container,
            $this->dispatcher,
            $this->fs,
            $this->onRun(...),
            $this->onSave(...),
            $this->onQueue(...),
        );
    }

    public function run(): void {
        $file        = null;
        $this->state = State::Preparation;

        foreach ($this->iterator as $item) {
            if (!$this->fs->exists($item)) {
                continue;
            }

            $item = $this->fs->get($item);

            if ($file === null) {
                $this->hook(Hook::Before, $item);

                $this->state = State::Iteration;
            }

            $this->file($item);

            $file = $item;
        }

        if ($file !== null) {
            $this->state = State::Finished;

            $this->hook(Hook::After, $file);
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
            if ($task instanceof FileTask) {
                $task($this->resolver, $file);
            } elseif ($task instanceof HookTask) {
                $task($this->resolver, $file, $hook);
            } else {
                throw new TaskNotInvokable($task, $hook, $file->path);
            }
        } catch (Exception $exception) {
            $result = TaskResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new TaskEnd($result));
        }
    }

    protected function queue(File $file): void {
        $this->iterator->push($file->path);
    }

    protected function onRun(File $file): void {
        // Possible?
        if ($this->state->is(State::Created)) {
            throw new DependencyUnavailable($file->path);
        }

        // Skipped?
        if ($this->isSkipped($file)) {
            return;
        }

        // Process
        if (!$this->state->is(State::Preparation) && !$file->path->equals(array_last($this->stack))) {
            $this->file($file);
        }
    }

    protected function onSave(File $file): void {
        // Current?
        if ($file->path->equals(array_last($this->stack))) {
            return;
        }

        // Skipped?
        if ($this->isSkipped($file)) {
            return;
        }

        // Reset
        unset($this->processed[$file->path->path]);

        // Run/Queue
        if ($this->state->is(State::Finished)) {
            $this->file($file);
        } else {
            $this->queue($file);
        }
    }

    protected function onQueue(File $file): void {
        // Possible?
        if ($this->state->is(State::Finished)) {
            throw new DependencyUnavailable($file->path);
        }

        // Skipped?
        if ($this->isSkipped($file)) {
            return;
        }

        // Queue
        $this->queue($file);
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
