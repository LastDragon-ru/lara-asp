<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Throwable;

use function sprintf;

class PathWriteFailed extends FileSystemError {
    public function __construct(
        protected readonly DirectoryPath|FilePath $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Path `%s` failed to write.',
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): DirectoryPath|FilePath {
        return $this->target;
    }
}
