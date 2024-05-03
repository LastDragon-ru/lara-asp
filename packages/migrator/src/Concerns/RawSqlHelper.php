<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\SchemaState;
use ReflectionClass;
use RuntimeException;

use function basename;
use function dirname;
use function file_get_contents;
use function is_file;
use function method_exists;

/**
 * @internal
 */
trait RawSqlHelper {
    protected function runRaw(string $type = null): void {
        $connection = $this->getConnectionInstance();
        $state      = $this->getSchemaState($connection);
        $path       = $this->getRawPath($type);

        if (is_file($path)) {
            if (!$connection->pretending()) {
                $state->load($path);
            }

            $connection->logQuery((string) file_get_contents($path), [], 0);
        }
    }

    protected function getRawPath(string $type = null): string {
        $path = (string) (new ReflectionClass($this))->getFileName();
        $file = basename($path, '.php');
        $dir  = dirname($path);

        return $type
            ? "{$dir}/{$file}~{$type}.sql"
            : "{$dir}/{$file}.sql";
    }

    abstract private function getConnectionInstance(): Connection;

    private function getSchemaState(Connection $connection): SchemaState {
        if (!method_exists($connection, 'getSchemaState')) {
            throw new RuntimeException('The database driver in use does not support SchemaState.');
        }

        return $connection->getSchemaState();
    }
}
