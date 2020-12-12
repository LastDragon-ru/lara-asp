<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;

/**
 * Sql Seeder. Allows use SQL as seed data.
 */
abstract class RawSeeder extends SmartSeeder {
    use RawSqlHelper;

    protected Application $app;
    protected Filesystem  $files;

    public function __construct(SeederService $service, Application $app, Filesystem $files) {
        parent::__construct($service);

        $this->app   = $app;
        $this->files = $files;
    }

    // <editor-fold desc="\LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper">
    // =========================================================================
    protected function getApplication(): Application {
        return $this->app;
    }

    protected function getFilesystem(): Filesystem {
        return $this->files;
    }
    // </editor-fold>

    // <editor-fold desc="Seed">
    // =========================================================================
    public function seed(): void {
        $this->runRaw();
    }
    // </editor-fold>
}
