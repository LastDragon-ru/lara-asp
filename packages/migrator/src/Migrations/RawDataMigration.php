<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Seeders\SeederService;

/**
 * Migration that contains data only.
 *
 * This migration is useful to insert data into the existing working application
 * and unlike {@link \LastDragon_ru\LaraASP\Migrator\Migrations\RawMigration} it
 * will be applied only if the database already seeded.
 */
abstract class RawDataMigration extends RawMigration {
    protected SeederService $seeder;

    public function __construct(Application $app, Filesystem $files, SeederService $seeder) {
        parent::__construct($app, $files);

        $this->seeder = $seeder;
    }

    // <editor-fold desc="\Illuminate\Database\Migrations\Migration">
    // =========================================================================
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if ($this->seeder->isSeeded()) {
            parent::up();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if ($this->seeder->isSeeded()) {
            parent::down();
        }
    }
    // </editor-fold>
}
