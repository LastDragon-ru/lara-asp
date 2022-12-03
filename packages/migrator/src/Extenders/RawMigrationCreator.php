<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Database\Migrations\MigrationCreator;

use function basename;
use function dirname;

class RawMigrationCreator extends MigrationCreator {
    // <editor-fold desc="Illuminate\Database\Migrations\MigrationCreator">
    // =========================================================================
    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     */
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
    protected function getStub($table, $create) {
        $path = $this->customStubPath.'/migration.stub';

        if (!$this->files->exists($path)) {
            if ($this->isAnonymousMigrationsSupported()) {
                $path = __DIR__.'/../../stubs/migration-anonymous.stub';
            } else {
                $path = __DIR__.'/../../stubs/migration.stub';
            }
        }

        return $this->files->get($path);
    }

    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    /**
     * @return array<string>
     */
    protected function getRawFiles(string $path): array {
        $dir  = dirname($path);
        $name = basename($path, '.php');

        return [
            "{$dir}/{$name}~up.sql",
            "{$dir}/{$name}~down.sql",
        ];
    }

    protected function isAnonymousMigrationsSupported(): bool {
        return SmartMigrator::isAnonymousMigrationsSupported();
    }
    // </editor-fold>
}
