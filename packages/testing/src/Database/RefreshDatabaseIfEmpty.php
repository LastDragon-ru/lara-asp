<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

/**
 * Unlike {@link \Illuminate\Foundation\Testing\RefreshDatabase} will refresh
 * the database only if it empty (very useful for local testing).
 *
 * @required {@link \Illuminate\Foundation\Testing\TestCase}
 *
 * @property-read \Illuminate\Foundation\Application $app
 *
 * @mixin \PHPUnit\Framework\TestCase
 */
trait RefreshDatabaseIfEmpty {
    use RefreshDatabase {
        refreshTestDatabase as protected laravelRefreshTestDatabase;
    }

    protected function refreshTestDatabase(): void {
        if (!RefreshDatabaseState::$migrated) {
            $connection = $this->app->make('db')->connection();
            $tables     = $connection->getDoctrineSchemaManager()->listTableNames();

            if ($tables) {
                RefreshDatabaseState::$migrated = true;
            }
        }

        $this->laravelRefreshTestDatabase();
    }
}
