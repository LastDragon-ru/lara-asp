<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Illuminate\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PHPUnit\Framework\TestCase;

/**
 * The trait is very similar to standard {@link RefreshDatabase} but there is one
 * difference: it will refresh the database only if it is empty. This is very
 * useful for local testing and allow significantly reduce bootstrap time.
 *
 * @deprecated %{VERSION} Please use own trait.
 *
 * @phpstan-require-extends TestCase
 */
trait RefreshDatabaseIfEmpty {
    use RefreshDatabase {
        refreshTestDatabase as protected laravelRefreshTestDatabase;
    }

    protected function refreshTestDatabase(): void {
        if (!RefreshDatabaseState::$migrated) {
            $connection = Container::getInstance()->make(DatabaseManager::class)->connection();
            $tables     = $connection->getSchemaBuilder()->getTables();

            if ($tables) {
                RefreshDatabaseState::$migrated = true;
            }
        }

        $this->laravelRefreshTestDatabase();
    }
}
