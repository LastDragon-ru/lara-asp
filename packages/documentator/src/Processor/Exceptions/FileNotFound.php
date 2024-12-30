<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use Throwable;

use function sprintf;

class FileNotFound extends FileSystemError {
    public function __construct(
        protected readonly FilePath|string $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'File `%s` does not exist.',
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): FilePath|string {
        return $this->target;
    }
}
