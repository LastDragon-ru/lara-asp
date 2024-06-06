<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Exceptions;

use Illuminate\Database\Connection;
use LastDragon_ru\LaraASP\Migrator\PackageException;
use Throwable;

use function sprintf;

class SchemaStateUnsupported extends PackageException {
    public function __construct(
        private readonly Connection $connection,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The database driver `%1$s` does not support SchemaState (connection `%2$s`).',
                $this->connection->getDriverName(),
                $this->connection->getName(),
            ),
            $previous,
        );
    }

    public function getConnection(): Connection {
        return $this->connection;
    }
}
