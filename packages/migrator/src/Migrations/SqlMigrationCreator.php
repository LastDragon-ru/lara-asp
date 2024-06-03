<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Database\Migrations\MigrationCreator;
use Override;

use function dirname;
use function pathinfo;

use const PATHINFO_FILENAME;

class SqlMigrationCreator extends MigrationCreator {
    // <editor-fold desc="Illuminate\Database\Migrations\MigrationCreator">
    // =========================================================================
    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    #[Override]
    public function create($name, $path, $table = null, $create = false) {
        $path  = parent::create($name, $path, $table, $create);
        $files = $this->getSqlFiles($path);

        foreach ($files as $file) {
            $this->files->put($file, '');
        }

        return $path;
    }

    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    #[Override]
    protected function getStub($table, $create) {
        $path = $this->customStubPath.'/SqlMigration.stub';

        if (!$this->files->exists($path)) {
            $path = __DIR__.'/../../stubs/SqlMigration.stub';
        }

        return $this->files->get($path);
    }

    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    /**
     * @return list<string>
     */
    protected function getSqlFiles(string $path): array {
        $dir  = dirname($path);
        $name = pathinfo($path, PATHINFO_FILENAME);

        return [
            "{$dir}/{$name}~up.sql",
            "{$dir}/{$name}~down.sql",
        ];
    }
    // </editor-fold>
}
