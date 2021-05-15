<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;

/**
 * Sql Seeder. Allows use SQL as seed data.
 */
abstract class RawSeeder extends SmartSeeder {
    use RawSqlHelper;

    protected Filesystem $files;

    public function __construct(SeederService $service, Filesystem $files) {
        parent::__construct($service);

        $this->files = $files;
    }

    // <editor-fold desc="\LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper">
    // =========================================================================
    protected function getContainer(): Container {
        return $this->container;
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
