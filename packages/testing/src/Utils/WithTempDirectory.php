<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use LastDragon_ru\LaraASP\Migrator\Package;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function mkdir;
use function register_shutdown_function;
use function rmdir;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

trait WithTempDirectory {
    protected function getTempDirectory(): string {
        $pkg  = Package::Name;
        $path = tempnam(sys_get_temp_dir(), $pkg);

        unlink($path);
        mkdir($path);

        register_shutdown_function(static function () use ($path): void {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($files as $file) {
                /** @var \SplFileInfo $file */
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            rmdir($path);
        });

        return $path;
    }
}
