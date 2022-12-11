<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Migrator\Seeders\SeederService;

/**
 * Migration that contains data only.
 *
 * This migration is useful to insert data into the existing working application
 * and unlike {@link \LastDragon_ru\LaraASP\Migrator\Migrations\RawMigration} it
 * will be applied only if the database already seeded.
 */
abstract class RawDataMigration extends RawMigration {
    // <editor-fold desc="Migration">
    // =========================================================================
    /**
     * Run the migrations.
     */
    public function up(): void {
        if ($this->isSeeded()) {
            parent::up();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        if ($this->isSeeded()) {
            parent::down();
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function isSeeded(): bool {
        return Container::getInstance()->make(SeederService::class)->isSeeded();
    }
    // </editor-fold>
}
