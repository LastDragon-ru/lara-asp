<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;
use Override;

/**
 * Sql Seeder. Allows use SQL as seed data.
 *
 * @deprecated %{VERSION} Please use {@see SqlSeeder} instead.
 */
abstract class RawSeeder extends SmartSeeder {
    use RawSqlHelper;

    #[Override]
    public function seed(): void {
        $this->runRaw();
    }
}
