<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use Throwable;

use function sprintf;

class DirectoryNotFound extends FileSystemError {
    public function __construct(
        protected readonly DirectoryPath|string $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Directory `%s` does not exist.',
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): DirectoryPath|string {
        return $this->target;
    }
}
