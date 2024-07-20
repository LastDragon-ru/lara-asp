<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use LastDragon_ru\LaraASP\Testing\Package;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

use function register_shutdown_function;
use function sys_get_temp_dir;

/**
 * Allows to create a temporary file. The file will be removed automatically
 * after script shutdown.
 */
trait WithTempFile {
    public static function getTempFile(?string $content = null, string $suffix = ''): SplFileInfo {
        $fs   = new Filesystem();
        $pkg  = Package::Name;
        $path = $fs->tempnam(sys_get_temp_dir(), $pkg, $suffix);
        $file = new SplFileInfo($path);

        if ($content) {
            $fs->dumpFile($path, $content);
        }

        register_shutdown_function(static function () use ($fs, $path): void {
            $fs->remove($path);
        });

        return $file;
    }
}
