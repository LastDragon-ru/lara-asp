<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Migrator\Commands\RawMigration;
use LastDragon_ru\LaraASP\Migrator\Commands\RawSeeder;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Extenders\SmartMigrator;
use Override;

use function array_merge;

class Provider extends ServiceProvider implements DeferrableProvider {
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

    // <editor-fold desc="\Illuminate\Contracts\Support\DeferrableProvider">
    // =========================================================================
    /**
     * @return array<array-key, string>
     */
    #[Override]
    public function provides(): array {
        return array_merge(parent::provides(), ['migrator', 'command.seeder.make', RawMigrationCreator::class]);
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function registerMigrator(): void {
        $this->app->singleton('migrator', static function (Container $container): Migrator {
            return new SmartMigrator(
                $container->make('migration.repository'),
                $container->make(ConnectionResolverInterface::class),
                $container->make(Filesystem::class),
                $container->make(Dispatcher::class),
            );
        });
    }

    protected function registerMigrationCreator(): void {
        $this->app->bind(RawMigrationCreator::class, static function (Application $app): MigrationCreator {
            return new RawMigrationCreator($app->make(Filesystem::class), $app->basePath('stubs'));
        });
    }
    // </editor-fold>
}
