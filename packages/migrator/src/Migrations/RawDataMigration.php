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
    public function __construct(
        Application|null $app = null,
        Filesystem|null $files = null,
        protected SeederService|null $seeder = null,
    ) {
        parent::__construct($app, $files);

        $this->seeder ??= $this->app->make(SeederService::class);
    }

    // <editor-fold desc="\Illuminate\Database\Migrations\Migration">
    // =========================================================================
    /**
     * Run the migrations.
     */
    public function up(): void {
        if ($this->seeder->isSeeded()) {
            parent::up();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        if ($this->seeder->isSeeded()) {
            parent::down();
        }
    }
    // </editor-fold>
}
