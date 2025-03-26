<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

/**
 * @implements Dependency<Directory>
 */
class Input implements Dependency {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): Directory {
        return $fs->getDirectory($fs->input);
    }

    #[Override]
    public function getPath(FileSystem $fs): DirectoryPath {
        return $fs->input;
    }
}
