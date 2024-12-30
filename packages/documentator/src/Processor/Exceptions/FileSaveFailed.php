<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use Throwable;

use function sprintf;

class FileSaveFailed extends FileSystemError {
    public function __construct(
        protected readonly FilePath|string $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to save `%s` file.',
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): FilePath|string {
        return $this->target;
    }
}
