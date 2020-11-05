<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;

abstract class RawMigration extends Migration {
    use RawSqlHelper;

    protected Application $app;
    protected Filesystem  $files;

    public function __construct(Application $app, Filesystem $files) {
        $this->app   = $app;
        $this->files = $files;
    }

    // <editor-fold desc="\Illuminate\Database\Migrations\Migration">
    // =========================================================================
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->runRawUp();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->runRawDown();
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function runRawUp() {
        $this->runRaw('up');
    }

    protected function runRawDown() {
        $this->runRaw('down');
    }
    // </editor-fold>
}
