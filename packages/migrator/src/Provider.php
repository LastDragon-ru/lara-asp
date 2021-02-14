<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawSeederMakeCommand;
use LastDragon_ru\LaraASP\Migrator\Extenders\SmartMigrator;

class Provider extends ServiceProvider implements DeferrableProvider {
    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function register() {
        parent::register();

        $this->registerMigrator();
        $this->registerMigrationCreator();
        $this->registerSeederMakeCommand();
    }
    // </editor-fold>

    // <editor-fold desc="\Illuminate\Contracts\Support\DeferrableProvider">
    // =========================================================================
    /**
     * @inheritdoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function provides() {
        return [...parent::provides(), 'migrator', 'migration.creator', 'command.seeder.make'];
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function registerMigrator(): void {
        $this->app->singleton('migrator', static function (Application $app): Migrator {
            return new SmartMigrator($app['migration.repository'], $app['db'], $app['files'], $app['events'], $app);
        });
    }

    protected function registerMigrationCreator(): void {
        $this->app->singleton('migration.creator', static function (Application $app): MigrationCreator {
            return new RawMigrationCreator($app['files'], $app->basePath('stubs'));
        });
    }

    protected function registerSeederMakeCommand(): void {
        $this->app->singleton('command.seeder.make', static function (Application $app): SeederMakeCommand {
            return new RawSeederMakeCommand($app['files']);
        });
    }
    // </editor-fold>
}
