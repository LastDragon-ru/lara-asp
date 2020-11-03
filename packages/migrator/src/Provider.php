<?php

namespace LastDragon_ru\LaraASP\Migrator;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawMigrationCreator;

class Provider extends ServiceProvider implements DeferrableProvider {
    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    public function register() {
        parent::register();

        $this->registerMigrationCreator();
    }
    // </editor-fold>

    // <editor-fold desc="\Illuminate\Contracts\Support\DeferrableProvider">
    // =========================================================================
    public function provides() {
        return [...parent::provides(), 'migration.creator'];
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function registerMigrationCreator() {
        $this->app->singleton('migration.creator', function (Application $app) {
            return new RawMigrationCreator($app['files'], $app->basePath('stubs'));
        });
    }
    // </editor-fold>
}
