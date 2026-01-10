<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Illuminate\Database\Console\Seeds\SeederMakeCommand;

/**
 * @internal
 */
class SqlSeederHelper extends SeederMakeCommand {
    public function getRootNamespace(): string {
        return parent::rootNamespace();
    }

    public function getSeedersPath(string $name): string {
        return $this->getPath($name);
    }
}
