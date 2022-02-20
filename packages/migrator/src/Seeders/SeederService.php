<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;

use function in_array;
use function is_string;

class SeederService {
    /**
     * @var array<string>
     */
    protected array $seedersPaths = [];

    public function __construct(
        protected Container $container,
    ) {
        // empty
    }

    // <editor-fold desc="API">
    // =========================================================================
    public function isSeeded(): bool {
        $seeded  = false;
        $tables  = $this->getConnection()->getDoctrineSchemaManager()->listTableNames();
        $skipped = [
            $this->container->make('config')->get('database.migrations'),
        ];

        foreach ($tables as $table) {
            if (in_array($table, $skipped, true)) {
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
    protected function getConnection(): Connection {
        return $this->container->make('db')->connection();
    }
    // </editor-fold>
}
