<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Migrator\Commands\RawMigration;
use LastDragon_ru\LaraASP\Migrator\Commands\RawSeeder;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Extenders\SmartMigrator;
use Override;

class Provider extends ServiceProvider {
    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    public function register() {
        parent::register();

        $this->registerMigrator();
        $this->registerMigrationCreator();
    }

    public function boot(): void {
        $this->commands(
            RawMigration::class,
            RawSeeder::class,
        );
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function registerMigrator(): void {
        $this->app->extend('migrator', static function (Migrator $migrator): Migrator {
            return SmartMigrator::create($migrator);
        });
    }

    protected function registerMigrationCreator(): void {
        $this->app->bindIf(RawMigrationCreator::class, static function (Application $app): MigrationCreator {
            return new RawMigrationCreator($app->make(Filesystem::class), $app->basePath('stubs'));
        });
    }
    // </editor-fold>
}
