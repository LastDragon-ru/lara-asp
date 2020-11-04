<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;

class RawMigration extends Migration {
    use RawSqlHelper;

    protected ConnectionResolverInterface $connections;
    protected Filesystem                  $files;

    public function __construct(
        ConnectionResolverInterface $resolver,
        Filesystem $files
    ) {
        $this->connections = $resolver;
        $this->files       = $files;
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
