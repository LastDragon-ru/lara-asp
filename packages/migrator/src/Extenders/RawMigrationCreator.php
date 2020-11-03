<?php

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Database\Migrations\MigrationCreator;

class RawMigrationCreator extends MigrationCreator {
    // <editor-fold desc="Illuminate\Database\Migrations\MigrationCreator">
    // =========================================================================
    public function create($name, $path, $table = null, $create = false) {
        $path = parent::create($name, $path, $table, $create);
        $raws = $this->getRawFiles($path);

        foreach ($raws as $raw) {
            $this->files->put($raw, '');
        }

        return $path;
    }

    protected function getStub($table, $create) {
        return $this->files->get(__DIR__.'/../../stubs/migration.stub');
    }

    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function getRawFiles(string $path): array {
        $dir  = dirname($path);
        $name = basename($path, '.php');

        return [
            "{$dir}/{$name}~up.sql",
            "{$dir}/{$name}~down.sql",
        ];
    }
    // </editor-fold>
}
