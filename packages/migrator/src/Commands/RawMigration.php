<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Support\Composer;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Package;

use function str_replace;

class RawMigration extends MigrateMakeCommand {
    public const Name = Package::Name.':raw-migration';

    public function __construct(RawMigrationCreator $creator, Composer $composer) {
        $this->signature = str_replace('make:migration', static::Name, $this->signature);

        parent::__construct($creator, $composer);
    }
}
