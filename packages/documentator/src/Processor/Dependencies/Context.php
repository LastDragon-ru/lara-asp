<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Hook;
use Override;

/**
 * @implements Dependency<File>
 */
readonly class Context implements Dependency {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): File {
        return $fs->getFile(Hook::Context);
    }

    #[Override]
    public function getPath(FileSystem $fs): File {
        return ($this)($fs);
    }
}
