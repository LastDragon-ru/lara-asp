<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Concerns;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\SchemaState;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use RuntimeException;

use function basename;
use function dirname;
use function method_exists;

/**
 * @internal
 */
trait RawSqlHelper {
    abstract protected function getApplication(): Application;

    abstract protected function getFilesystem(): Filesystem;

    protected function runRaw(string $type = null): void {
        $connection = $this->getConnectionInstance();
        $state      = $this->getSchemaState($connection);
        $path       = $this->getRawPath($type);

        if ($this->getFilesystem()->isFile($path)) {
            if (!$connection->pretending()) {
                $state->load($path);
            }

            $connection->logQuery($this->getFilesystem()->get($path), [], 0);
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
        $connection = $this->getApplication()->make('db')->connection($connection);

        return $connection;
    }

    private function getSchemaState(Connection $connection): SchemaState {
        if (!method_exists($connection, 'getSchemaState')) {
            throw new RuntimeException('The database driver in use does not support SchemaState.');
        }

        return $connection->getSchemaState();
    }
}
