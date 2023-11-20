<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Database\Migrations\MigrationCreator;
use Override;

use function basename;
use function dirname;

class RawMigrationCreator extends MigrationCreator {
    // <editor-fold desc="Illuminate\Database\Migrations\MigrationCreator">
    // =========================================================================
    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    #[Override]
    public function create($name, $path, $table = null, $create = false) {
        $path = parent::create($name, $path, $table, $create);
        $raws = $this->getRawFiles($path);

        foreach ($raws as $raw) {
            $this->files->put($raw, '');
        }

        return $path;
    }

    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    #[Override]
    protected function getStub($table, $create) {
        $path = $this->customStubPath.'/migration.stub';

        if (!$this->files->exists($path)) {
            $path = __DIR__.'/../../stubs/migration.stub';
        }

        return $this->files->get($path);
    }

    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    /**
     * @return list<string>
     */
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
