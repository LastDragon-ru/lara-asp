<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration;
use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;
use Override;

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

    // <editor-fold desc="RawSqlHelper">
    // =========================================================================
    #[Override]
    private function getConnectionInstance(): Connection {
        return Container::getInstance()->make(DatabaseManager::class)->connection($this->getConnection());
    }
    // </editor-fold>
}
