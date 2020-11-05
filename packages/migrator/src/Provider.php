<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawSeederMakeCommand;
use LastDragon_ru\LaraASP\Migrator\Extenders\SmartMigrator;

class Provider extends ServiceProvider implements DeferrableProvider {
    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    public function register() {
        parent::register();

        $this->registerMigrator();
        $this->registerMigrationCreator();
        $this->registerSeederMakeCommand();
    }
    // </editor-fold>

    // <editor-fold desc="\Illuminate\Contracts\Support\DeferrableProvider">
    // =========================================================================
    public function provides() {
        return [...parent::provides(), 'migrator', 'migration.creator', 'command.seeder.make'];
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function registerMigrator() {
        $this->app->singleton('migrator', function (Application $app) {
            return new SmartMigrator($app['migration.repository'], $app['db'], $app['files'], $app['events'], $app);
        });
    }

    protected function registerMigrationCreator() {
        $this->app->singleton('migration.creator', function (Application $app) {
            return new RawMigrationCreator($app['files'], $app->basePath('stubs'));
        });
    }

    protected function registerSeederMakeCommand(): void {
        $this->app->singleton('command.seeder.make', function (Application $app) {
            return new RawSeederMakeCommand($app['files']);
        });
    }
    // </editor-fold>
}
