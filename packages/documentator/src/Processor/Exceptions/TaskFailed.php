<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Throwable;

use function sprintf;

class TaskFailed extends ProcessorError {
    public function __construct(
        protected readonly FileSystem $filesystem,
        protected readonly File $target,
        protected readonly Task $task,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The `%s` task failed for `%s` file.',
                $this->task::class,
                $this->filesystem->getPathname($this->target),
            ),
            $previous,
        );
    }

    public function getFilesystem(): FileSystem {
        return $this->filesystem;
    }

    public function getTarget(): File {
        return $this->target;
    }

    public function getTask(): Task {
        return $this->task;
    }
}
