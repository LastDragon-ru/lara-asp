<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Throwable;

use function sprintf;

class DependencyUnresolvable extends DependencyError {
    public function __construct(
        protected readonly DirectoryPath|FilePath $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Dependency `%s` cannot be resolved.',
                $this->target,
            ),
            $previous,
        );
    }
}
