<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Illuminate\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PHPUnit\Framework\TestCase;

/**
 * Unlike {@link \Illuminate\Foundation\Testing\RefreshDatabase} will refresh
 * the database only if it empty (very useful for local testing).
 *
 * @required {@link \Illuminate\Foundation\Testing\TestCase}
 *
 * @property-read Application $app
 *
 * @mixin TestCase
 */
trait RefreshDatabaseIfEmpty {
    use RefreshDatabase {
        refreshTestDatabase as protected laravelRefreshTestDatabase;
    }

    protected function refreshTestDatabase(): void {
        if (!RefreshDatabaseState::$migrated) {
            $connection = Container::getInstance()->make(DatabaseManager::class)->connection();
            $tables     = $connection->getDoctrineSchemaManager()->listTableNames();

            if ($tables) {
                RefreshDatabaseState::$migrated = true;
            }
        }

        $this->laravelRefreshTestDatabase();
    }
}
