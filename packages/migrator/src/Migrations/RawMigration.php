<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;

use function app;

abstract class RawMigration extends Migration {
    use RawSqlHelper;

    public function __construct(
        protected Container|null $container = null,
        protected Filesystem|null $files = null,
    ) {
        $this->container ??= app();
        $this->files     ??= $this->getContainer()->make(Filesystem::class);
    }

    // <editor-fold desc="\LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper">
    // =========================================================================
    protected function getContainer(): Container {
        return $this->container;
    }

    protected function getFilesystem(): Filesystem {
        return $this->files;
    }
    // </editor-fold>

    // <editor-fold desc="\Illuminate\Database\Migrations\Migration">
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
