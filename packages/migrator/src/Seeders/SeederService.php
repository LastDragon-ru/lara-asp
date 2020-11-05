<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class SeederService {
    protected Application $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    // <editor-fold desc="API">
    // =========================================================================
    public function isSeeded(): bool {
        $seeded  = false;
        $tables  = $this->getConnection()->getDoctrineSchemaManager()->listTableNames();
        $skipped = [
            $this->app->make('config')->get('database.migrations'),
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
     * @param string|\Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    public function isModelSeeded($model): bool {
        if (is_string($model) && is_subclass_of($model, Model::class, true)) {
            $model = new $model();
        }

        if ($model instanceof Model) {
            $model = $model->getTable();
        } else {
            throw new InvalidArgumentException('The `$target` should be model or model class name.');
        }

        return $this->isTableSeeded($model);
    }

    public function isTableSeeded(string $table): bool {
        return $this->getConnection()->table($table)->count() > 0;
    }

    public function loadSeedsFrom(string $path): void {
        $this->app->afterResolving(DatabaseSeeder::class, function (DatabaseSeeder $seeder) use ($path) {
            $seeder->addPath($path);
        });
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function getConnection(): Connection {
        return $this->app->make('db')->connection();
    }
    // </editor-fold>
}
