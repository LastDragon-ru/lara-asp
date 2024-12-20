<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

/**
 * @implements Dependency<File>
 */
class FileReference implements Dependency {
    public function __construct(
        protected readonly File|FilePath|string $reference,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): mixed {
        // Already?
        if ($this->reference instanceof File) {
            return $this->reference;
        }

        // Create
        $resolved = $fs->getFile($fs->input->getFilePath((string) $this));

        if ($resolved === null) {
            throw new DependencyUnresolvable($this);
        }

        return $resolved;
    }

    #[Override]
    public function __toString(): string {
        return (string) $this->reference;
    }
}
