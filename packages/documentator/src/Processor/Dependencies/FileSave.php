<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

/**
 * @implements Dependency<null>
 */
readonly class FileSave implements Dependency {
    public function __construct(
        protected File $file,
        protected object|string $content,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): mixed {
        $fs->write($this->file, $this->content);

        return null;
    }

    #[Override]
    public function getPath(FileSystem $fs): File {
        return $this->file;
    }
}
