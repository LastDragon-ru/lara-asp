<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use Exception;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Exceptions\TempFileFailed;

use function copy;
use function file_put_contents;
use function is_dir;
use function is_file;
use function sys_get_temp_dir;
use function unlink;

/**
 * Creates a temporary file in the system temp directory. The file will be
 * removed after the instance removal.
 */
readonly class TempFile {
    public FilePath $path;

    public function __construct(FilePath|string|null $source = null) {
        $dir  = sys_get_temp_dir();
        $path = null;

        try {
            foreach (new TempName() as $name) {
                // Exists?
                $variant = "{$dir}/{$name}";

                if (is_file($variant) || is_dir($variant)) {
                    continue;
                }

                // Nope
                if ($source instanceof FilePath) {
                    if (copy($source->path, $variant)) {
                        $path = $variant;
                    }
                } elseif (file_put_contents($variant, (string) $source) !== false) {
                    $path = $variant;
                } else {
                    $path = null;
                }

                break;
            }
        } catch (Exception $exception) {
            throw new TempFileFailed(new DirectoryPath($dir), $exception);
        }

        if ($path === null) {
            throw new TempFileFailed(new DirectoryPath($dir));
        }

        $this->path = new FilePath($path);
    }

    public function __destruct() {
        unlink($this->path->path);
    }
}
