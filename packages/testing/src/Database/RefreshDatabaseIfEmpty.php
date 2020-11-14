<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

/**
 * Unlike {@link \Illuminate\Foundation\Testing\RefreshDatabase} will refresh
 * the database only if it empty (very useful for local testing).
 *
 * @mixin \Illuminate\Foundation\Testing\TestCase
 */
trait RefreshDatabaseIfEmpty {
    use RefreshDatabase {
        refreshTestDatabase as protected laravelRefreshTestDatabase;
    }

    protected function refreshTestDatabase() {
        $connection = $this->app->make('db')->connection();
        $tables     = $connection->getDoctrineSchemaManager()->listTableNames();

        if ($tables) {
            RefreshDatabaseState::$migrated = true;
        }

        $this->laravelRefreshTestDatabase();
    }
}
