<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator as IlluminateMigrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Migrator\Commands\RawSeeder;
use LastDragon_ru\LaraASP\Migrator\Commands\SqlMigration;
use LastDragon_ru\LaraASP\Migrator\Migrations\Migrator;
use LastDragon_ru\LaraASP\Migrator\Migrations\SqlMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Seeders\SeederService;
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

        $this->registerSeeder();
        $this->registerMigrator();
        $this->registerMigrationCreator();
    }

    public function boot(): void {
        $this->commands(
            SqlMigration::class,
            RawSeeder::class,
        );
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function registerSeeder(): void {
        $this->app->scopedIf(SeederService::class);
    }

    protected function registerMigrator(): void {
        $this->app->alias('migrator', Migrator::class);
        $this->app->extend('migrator', static function (IlluminateMigrator $migrator): IlluminateMigrator {
            return Migrator::create($migrator);
        });
    }

    protected function registerMigrationCreator(): void {
        $this->app->scopedIf(SqlMigrationCreator::class, static function (Application $app): MigrationCreator {
            return new SqlMigrationCreator($app->make(Filesystem::class), $app->basePath('stubs'));
        });
    }
    // </editor-fold>
}
