<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use FilesystemIterator;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Exceptions\TempDirectoryFailed;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function assert;
use function copy;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

/**
 * Creates a temporary directory in the system temp directory. The directory will
 * be removed after the instance removal.
 */
class TempDirectory {
    public DirectoryPath $path;

    public function __construct(?DirectoryPath $source = null) {
        // Create
        $dir  = new DirectoryPath(sys_get_temp_dir());
        $path = null;

        foreach (new TempName() as $name) {
            $name = $dir->directory($name);

            if (@mkdir($name->path, 0700)) {
                $path = $name;
                break;
            }
        }

        if ($path === null) {
            throw new TempDirectoryFailed($dir);
        }

        // Copy
        if ($source !== null) {
            $iterator = new RecursiveDirectoryIterator($source->path, FilesystemIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($iterator as $info) {
                assert($info instanceof SplFileInfo, 'https://github.com/phpstan/phpstan/issues/8435');

                $result = false;
                $target = $info->getPathname();
                $target = $info->isDir() || $target === '' ? new DirectoryPath($target) : new FilePath($target);
                $target = $source->relative($target);

                if ($target !== null) {
                    $target = $path->resolve($target);
                    $result = $info->isDir() ? mkdir($target->path, 0700) : copy($info->getPathname(), $target->path);
                }

                if (!$result) {
                    throw new TempDirectoryFailed($dir);
                }
            }
        }

        // Save
        $this->path = $path;
    }

    public function __destruct() {
        $iterator = new RecursiveDirectoryIterator($this->path->path, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $info) {
            assert($info instanceof SplFileInfo, 'https://github.com/phpstan/phpstan/issues/8435');

            if ($info->isDir()) {
                rmdir($info->getPathname());
            } else {
                unlink($info->getPathname());
            }
        }

        rmdir($this->path->path);
    }
}
