<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\Path\Path;
use Symfony\Component\Finder\SplFileInfo;
use WeakMap;

/**
 * @internal
 */
class SymfonyPathMap {
    /**
     * @var WeakMap<SplFileInfo, DirectoryPath|FilePath>
     */
    private WeakMap $map;

    public function __construct() {
        $this->map = new WeakMap();
    }

    public function get(SplFileInfo $file): DirectoryPath|FilePath {
        if (!isset($this->map[$file])) {
            $path             = $file->getRelativePathname().($file->isDir() ? '/' : '');
            $this->map[$file] = Path::make($path)->normalized();
        }

        return $this->map[$file];
    }
}
