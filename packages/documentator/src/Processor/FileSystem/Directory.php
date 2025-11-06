<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Path\Path;

/**
 * @extends Entry<DirectoryPath>
 */
class Directory extends Entry {
    public function isInside(self|DirectoryPath|FilePath|File $path): bool {
        return $this->path->isInside(
            $path instanceof Path ? $path : $path->path,
        );
    }
}
