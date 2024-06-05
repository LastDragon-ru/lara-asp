<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use WeakMap;

use function array_column;
use function is_array;
use function mb_strtolower;

class SeederService {
    /**
     * @var WeakMap<Connection, true>
     */
    private WeakMap $connections;

    public function __construct(
        protected readonly ConfigResolver $config,
        protected readonly DatabaseManager $manager,
    ) {
        $this->connections = new WeakMap();
    }

    public function isSeeded(Connection|string|null $connection = null): bool {
        // Connection?
        $connection = $this->getConnection($connection);

        if (isset($this->connections[$connection])) {
            return true;
        }

        // Detect
        $seeded  = false;
        $tables  = array_column($connection->getSchemaBuilder()->getTables(), 'name');
        $default = 'migrations';
        $skipped = $this->config->getInstance()->get('database.migrations', $default);
        $skipped = is_array($skipped) ? ($skipped['table'] ?: $default) : $skipped;
        $skipped = mb_strtolower(Cast::toString($skipped));

        foreach ($tables as $table) {
            if ($skipped === mb_strtolower($table)) {
                continue;
            }

            if ($connection->table($table)->count() > 0) {
                $seeded = true;
                break;
            }
        }

        // Cache
        // Seeder is about of to fill the database, not to remove records. So we
        // are assuming that if the database is seeded, then this is permanent.
        $this->connections[$connection] = true;

        // Return
        return $seeded;
    }

    protected function getConnection(Connection|string|null $connection = null): Connection {
        return match (true) {
            $connection instanceof Connection => $connection,
            default                           => $this->manager->connection($connection),
        };
    }
}
