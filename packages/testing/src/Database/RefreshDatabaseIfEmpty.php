<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use LastDragon_ru\LaraASP\Testing\Package;
use PHPUnit\Framework\TestCase;

use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '6.2.0', 'Please use own trait.');

/**
 * The trait is very similar to standard {@link RefreshDatabase} but there is one
 * difference: it will refresh the database only if it is empty. This is very
 * useful for local testing and allow significantly reduce bootstrap time.
 *
 * @deprecated 6.2.0 Please use own trait.
 *
 * @phpstan-require-extends TestCase
 */
trait RefreshDatabaseIfEmpty {
    use RefreshDatabase {
        refreshTestDatabase as protected laravelRefreshTestDatabase;
    }

    abstract protected function app(): Application;

    protected function refreshTestDatabase(): void {
        if (!RefreshDatabaseState::$migrated) {
            $connection = $this->app()->make(DatabaseManager::class)->connection();
            $tables     = $connection->getSchemaBuilder()->getTables();

            if ($tables) {
                RefreshDatabaseState::$migrated = true;
            }
        }

        $this->laravelRefreshTestDatabase();
    }
}
