<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Database\Console\Seeds\SeederMakeCommand;

class RawSeederMakeCommand extends SeederMakeCommand {
    protected function resolveStubPath($stub) {
        $custom = $this->laravel->basePath(trim($stub, '/'));
        $path   = !$this->files->exists($custom)
            ? __DIR__.'/../../'.$stub
            : $custom;

        return $path;
    }
}
