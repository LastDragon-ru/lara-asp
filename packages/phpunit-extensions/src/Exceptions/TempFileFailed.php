<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Exceptions;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\PhpUnit\PackageException;
use Throwable;

use function sprintf;

class TempFileFailed extends PackageException {
    public function __construct(
        protected DirectoryPath $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Failed to create temp file in `%s`.', $this->target),
            $previous,
        );
    }
}
