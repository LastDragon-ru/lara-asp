<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Core\Utils\Cast;

use function array_column;
use function is_array;
use function is_string;
use function mb_strtolower;

class SeederService {
    public function __construct(
        protected readonly ConfigResolver $config,
        protected readonly DatabaseManager $manager,
    ) {
        // empty
    }

    // <editor-fold desc="API">
    // =========================================================================
    public function isSeeded(): bool {
        $seeded  = false;
        $tables  = array_column($this->getConnection()->getSchemaBuilder()->getTables(), 'name');
        $default = 'migrations';
        $skipped = $this->config->getInstance()->get('database.migrations', $default);
        $skipped = is_array($skipped) ? ($skipped['table'] ?: $default) : $skipped;
        $skipped = mb_strtolower(Cast::toString($skipped));

        foreach ($tables as $table) {
            if ($skipped === mb_strtolower($table)) {
                continue;
            }

            if ($this->isTableSeeded($table)) {
                $seeded = true;
                break;
            }
        }

        return $seeded;
    }

    /**
     * @param class-string<Model>|Model $model
     */
    public function isModelSeeded(string|Model $model): bool {
        if (is_string($model)) {
            $model = new $model();
        }

        return $this->isTableSeeded($model->getTable());
    }

    public function isTableSeeded(string $table): bool {
        return $this->getConnection()->table($table)->count() > 0;
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    public function getConnection(): Connection {
        return $this->manager->connection();
    }
    // </editor-fold>
}
