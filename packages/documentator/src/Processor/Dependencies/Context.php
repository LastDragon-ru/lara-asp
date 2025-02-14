<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Hooks\Hook;
use Override;

/**
 * @implements Dependency<File>
 */
readonly class Context implements Dependency {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): mixed {
        return $fs->getHook(Hook::Context);
    }

    #[Override]
    public function getPath(FileSystem $fs): Directory|DirectoryPath|File|FilePath {
        return ($this)($fs);
    }
}
