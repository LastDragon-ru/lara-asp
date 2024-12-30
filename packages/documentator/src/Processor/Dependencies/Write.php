<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;
use Stringable;

/**
 * @implements Dependency<File>
 */
readonly class Write implements Dependency {
    public function __construct(
        protected File|FilePath|string $file,
        protected Stringable|string $content,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): mixed {
        return $fs->write($this->file, (string) $this->content);
    }

    #[Override]
    public function getPath(): FilePath {
        return match (true) {
            $this->file instanceof FilePath => $this->file,
            $this->file instanceof File     => $this->file->getPath(),
            default                         => new FilePath($this->file),
        };
    }
}
