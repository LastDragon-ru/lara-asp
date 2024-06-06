<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Exceptions;

use LastDragon_ru\LaraASP\Migrator\PackageException;
use Throwable;

class ConnectionUnknown extends PackageException {
    public function __construct(Throwable $previous = null) {
        parent::__construct('Unknown connection.', $previous);
    }
}
