<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\SchemaState;
use ReflectionClass;
use RuntimeException;

/**
 * @internal
 *
 * @property \Illuminate\Database\ConnectionResolverInterface $connections
 * @property \Illuminate\Filesystem\Filesystem                $files
 */
trait RawSqlHelper {
    protected function runRaw(string $type = null) {
        $connection = $this->getConnectionInstance();
        $state      = $this->getSchemaState($connection);
        $path       = $this->getRawPath($type);

        if ($this->files->isFile($path)) {
            if (!$connection->pretending()) {
                $state->load($path);
            }

            $connection->logQuery($this->files->get($path), [], 0);
        }
    }

    protected function getRawPath(string $type = null): string {
        $path = (new ReflectionClass($this))->getFileName();
        $file = basename($path, '.php');
        $dir  = dirname($path);

        return $type
            ? "{$dir}/{$file}~{$type}.sql"
            : "{$dir}/{$file}.sql";
    }

    private function getConnectionInstance(): Connection {
        $connection = $this instanceof Migration
            ? $this->getConnection()
            : null;
        $connection = $this->connections->connection($connection);

        if (!($connection instanceof Connection)) {
            // Only `\Illuminate\Database\Connection` supported now
            //
            // https://github.com/laravel/framework/issues/35090
            throw new RuntimeException(sprintf('Connection must be instance of %s.', Connection::class));
        }

        return $connection;
    }

    private function getSchemaState(Connection $connection): SchemaState {
        if (!method_exists($connection, 'getSchemaState')) {
            throw new RuntimeException('The database driver in use does not support SchemaState.');
        }

        return $connection->getSchemaState();
    }
}
