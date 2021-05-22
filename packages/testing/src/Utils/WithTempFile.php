<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use LastDragon_ru\LaraASP\Migrator\Package;
use SplFileInfo;

use function file_put_contents;
use function register_shutdown_function;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

trait WithTempFile {
    protected function getTempFile(string $content = null): SplFileInfo {
        $pkg  = Package::Name;
        $path = tempnam(sys_get_temp_dir(), $pkg);
        $file = new SplFileInfo($path);

        if ($content) {
            file_put_contents($path, $content);
        }

        register_shutdown_function(static function () use ($path): void {
            unlink($path);
        });

        return $file;
    }
}
