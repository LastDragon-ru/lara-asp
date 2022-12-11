<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Database\Migrations\Migration;
use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;

abstract class RawMigration extends Migration {
    use RawSqlHelper;

    // <editor-fold desc="Migration">
    // =========================================================================
    /**
     * Run the migrations.
     */
    public function up(): void {
        $this->runRawUp();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        $this->runRawDown();
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function runRawUp(): void {
        $this->runRaw('up');
    }

    protected function runRawDown(): void {
        $this->runRaw('down');
    }
    // </editor-fold>
}
