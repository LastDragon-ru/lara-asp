<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

use function is_string;

/**
 * @implements Dependency<File>
 */
readonly class FileSave implements Dependency {
    public function __construct(
        protected File|FilePath|string $file,
        protected object|string $content,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): File {
        return $fs->write($this->getPath($fs), $this->content);
    }

    #[Override]
    public function getPath(FileSystem $fs): File|FilePath {
        return match (true) {
            $this->file instanceof File => $this->file,
            is_string($this->file)      => $fs->output->getPath(new FilePath($this->file)),
            default                     => $fs->output->getPath($this->file),
        };
    }
}
