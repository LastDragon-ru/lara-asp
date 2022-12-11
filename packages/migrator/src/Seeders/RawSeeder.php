<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;

/**
 * Sql Seeder. Allows use SQL as seed data.
 */
abstract class RawSeeder extends SmartSeeder {
    use RawSqlHelper;

    // <editor-fold desc="Seed">
    // =========================================================================
    public function seed(): void {
        $this->runRaw();
    }
    // </editor-fold>
}
