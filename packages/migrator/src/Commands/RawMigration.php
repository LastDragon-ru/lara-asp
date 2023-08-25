<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Support\Composer;
use LastDragon_ru\LaraASP\Migrator\Extenders\RawMigrationCreator;
use LastDragon_ru\LaraASP\Migrator\Package;
use Symfony\Component\Console\Attribute\AsCommand;

use function str_replace;

#[AsCommand(
    name: RawMigration::Name,
)]
class RawMigration extends MigrateMakeCommand {
    protected const Name = Package::Name.':raw-migration';

    public function __construct(RawMigrationCreator $creator, Composer $composer) {
        $this->signature = str_replace('make:migration', self::Name, $this->signature);

        parent::__construct($creator, $composer);
    }

    public static function getDefaultName(): ?string {
        return self::Name;
    }
}
