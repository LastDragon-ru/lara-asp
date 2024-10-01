<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Traits;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\SchemaState;
use LastDragon_ru\LaraASP\Migrator\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Migrator\Exceptions\SchemaStateUnsupported;

use function array_filter;
use function dirname;
use function file_get_contents;
use function implode;
use function is_file;
use function method_exists;
use function pathinfo;

use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;

/**
 * @internal
 */
trait SqlHelper {
    protected function runSqlFile(Connection $connection, string $path): void {
        $state = $this->getSchemaState($connection);

        if (is_file($path)) {
            if (!$connection->pretending()) {
                $state->load($path);
            }

            $connection->logQuery((string) file_get_contents($path), [], 0);
        } else {
            throw new FileNotFound($path);
        }
    }

    protected function getSqlPath(string $path, ?string ...$type): string {
        $sql       = 'sql';
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension !== $sql) {
            $directory = dirname($path);
            $type      = implode('.', array_filter($type, static fn ($type) => $type !== null && $type !== ''));
            $name      = pathinfo($path, PATHINFO_FILENAME);
            $path      = $type !== ''
                ? "{$directory}/{$name}~{$type}.{$sql}"
                : "{$directory}/{$name}.{$sql}";
        }

        return $path;
    }

    private function getSchemaState(Connection $connection): SchemaState {
        if (!method_exists($connection, 'getSchemaState')) {
            throw new SchemaStateUnsupported($connection);
        }

        return $connection->getSchemaState();
    }
}
