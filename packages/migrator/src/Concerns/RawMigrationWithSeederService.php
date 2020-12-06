<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Concerns;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Seeders\SeederService;

/**
 * @deprecated probably you need RawDataMigration
 * @mixin \LastDragon_ru\LaraASP\Migrator\Migrations\RawMigration
 */
trait RawMigrationWithSeederService {
    protected SeederService $seeder;

    public function __construct(Application $app, Filesystem $files, SeederService $seeder) {
        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct($app, $files);

        $this->seeder = $seeder;
    }
}
