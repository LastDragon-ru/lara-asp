<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use LastDragon_ru\LaraASP\Migrator\Traits\SqlHelper;
use ReflectionClass;

/**
 * SQL Seeder. Allows using SQL as seed data.
 */
abstract class SqlSeeder extends Seeder {
    use SqlHelper;

    public function run(): void {
        $connection = $this->service->getConnection($this->getConnection());
        $path       = (string) (new ReflectionClass($this))->getFileName();

        $this->runSqlFile($connection, $path);
    }
}
