<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

use function is_string;

/**
 * @implements Dependency<File>
 */
readonly class FileReference implements Dependency {
    public function __construct(
        protected FilePath|string $reference,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): File {
        try {
            return $fs->getFile($this->reference);
        } catch (FileNotFound $exception) {
            throw new DependencyUnresolvable($exception);
        }
    }

    #[Override]
    public function getPath(FileSystem $fs): FilePath {
        return is_string($this->reference)
            ? new FilePath($this->reference)
            : $this->reference;
    }
}
