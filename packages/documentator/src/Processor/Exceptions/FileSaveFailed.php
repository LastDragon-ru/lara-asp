<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Throwable;

use function sprintf;

class FileSaveFailed extends ProcessorError {
    public function __construct(
        protected readonly FileSystem $filesystem,
        protected readonly File $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to save `%s` file.',
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
}
