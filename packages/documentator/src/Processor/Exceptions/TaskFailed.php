<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Hook;
use LastDragon_ru\Path\FilePath;
use Throwable;

use function sprintf;

class TaskFailed extends TaskError {
    public function __construct(
        protected readonly Task $task,
        protected readonly Hook $hook,
        protected readonly FilePath $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The `%s` task failed for `%s` file (`%s` hook).',
                $this->task::class,
                $this->target,
                $this->hook->name,
            ),
            $previous,
        );
    }

    public function getTask(): Task {
        return $this->task;
    }

    public function getHook(): Hook {
        return $this->hook;
    }

    public function getTarget(): FilePath {
        return $this->target;
    }
}
