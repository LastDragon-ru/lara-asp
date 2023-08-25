<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Package;
use Symfony\Component\Console\Attribute\AsCommand;

use function basename;
use function dirname;
use function trim;

#[AsCommand(
    name: RawSeeder::Name,
)]
class RawSeeder extends SeederMakeCommand {
    protected const Name = Package::Name.':raw-seeder';

    public function __construct(Filesystem $filesystem) {
        $this->name = self::Name;

        parent::__construct($filesystem);
    }

    public static function getDefaultName(): ?string {
        return self::Name;
    }

    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function resolveStubPath($stub) {
        $custom = $this->laravel->basePath(trim($stub, '/'));
        $path   = !$this->files->exists($custom)
            ? __DIR__.'/../../'.$stub
            : $custom;

        return $path;
    }

    /**
     * @inheritDoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function makeDirectory($path) {
        // FIXME [migrator] `make:seeder` hack: would be good to use another
        //      way to add file(s) after the command finished.
        $path = parent::makeDirectory($path);
        $dir  = dirname($path);
        $name = basename($path, '.php');

        $this->files->put("{$dir}/{$name}.sql", '');

        return $path;
    }
}
