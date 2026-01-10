<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Throwable;

use function sprintf;

class DependencyUnavailable extends DependencyError {
    public function __construct(
        protected readonly DirectoryPath|FilePath $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Dependency `%s` is not available.',
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): DirectoryPath|FilePath {
        return $this->target;
    }
}
