<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Exceptions;

use LastDragon_ru\LaraASP\Migrator\PackageException;
use Throwable;

use function sprintf;

class FileNotFound extends PackageException {
    public function __construct(
        private readonly string $path,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('The SQL file `%s` does not exist.', $this->path),
            $previous,
        );
    }

    public function getPath(): string {
        return $this->path;
    }
}
