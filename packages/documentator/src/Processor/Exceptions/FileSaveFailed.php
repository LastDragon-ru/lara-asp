<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class FileSaveFailed extends ProcessorError {
    public function __construct(
        protected Directory $root,
        protected readonly File $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to save `%s` file (root: `%s`).',
                $this->target->getRelativePath($this->root),
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
}
