<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithCommands;
use LastDragon_ru\LaraASP\Migrator\Commands\RawMigration;
use LastDragon_ru\LaraASP\Migrator\Commands\RawSeeder;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Extenders\SmartMigrator;

class Provider extends ServiceProvider implements DeferrableProvider {
    use ProviderWithCommands;

    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function register() {
        parent::register();

        $this->registerMigrator();
        $this->registerMigrationCreator();
    }

    public function boot(): void {
        $this->bootCommands(
            RawMigration::class,
            RawSeeder::class,
        );
    }
    // </editor-fold>

    // <editor-fold desc="\Illuminate\Contracts\Support\DeferrableProvider">
    // =========================================================================
    /**
     * @inheritdoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function provides() {
        return [...parent::provides(), 'migrator', 'command.seeder.make', RawMigrationCreator::class];
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function registerMigrator(): void {
        $this->app->singleton('migrator', static function (Application $app): Migrator {
            return new SmartMigrator($app['migration.repository'], $app['db'], $app['files'], $app['events']);
        });
    }

    protected function registerMigrationCreator(): void {
        $this->app->bind(RawMigrationCreator::class, static function (Application $app): MigrationCreator {
            return new RawMigrationCreator($app['files'], $app->basePath('stubs'));
        });
    }
    // </editor-fold>
}
