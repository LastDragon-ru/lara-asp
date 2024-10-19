<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class FileTaskFailed extends ProcessorError {
    public function __construct(
        protected readonly Directory $root,
        protected readonly File $target,
        protected readonly Task $task,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The `%s` task failed for `%s` file (root: `%s`).',
                $this->task::class,
                $this->root->getRelativePath($this->target),
                $this->root->getPath(),
            ),
            $previous,
        );
    }

    public function getRoot(): Directory {
        return $this->root;
    }

    public function getTarget(): File {
        return $this->target;
    }

    public function getTask(): Task {
        return $this->task;
    }
}
