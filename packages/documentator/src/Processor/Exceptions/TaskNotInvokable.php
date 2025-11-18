<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Hook;
use Throwable;

use function sprintf;

class TaskNotInvokable extends TaskError {
    public function __construct(
        protected readonly Task $task,
        protected readonly Hook $hook,
        protected readonly File $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The `%s` task cannot be invoked for `%s` file (`%s` hook).',
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

    public function getTarget(): File {
        return $this->target;
    }
}
