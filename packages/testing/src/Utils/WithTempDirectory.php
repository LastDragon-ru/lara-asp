<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use LastDragon_ru\LaraASP\Testing\Package;
use Symfony\Component\Filesystem\Filesystem;

use function register_shutdown_function;
use function sys_get_temp_dir;

trait WithTempDirectory {
    protected function getTempDirectory(): string {
        $fs   = new Filesystem();
        $pkg  = Package::Name;
        $path = $fs->tempnam(sys_get_temp_dir(), $pkg);

        $fs->remove($path);
        $fs->mkdir($path);

        register_shutdown_function(static function () use ($fs, $path): void {
            $fs->remove($path);
        });

        return $path;
    }
}
