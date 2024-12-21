<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

/**
 * @implements Dependency<Directory>
 */
class DirectoryReference implements Dependency {
    public function __construct(
        protected readonly DirectoryPath|string $reference,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): mixed {
        // Create
        $resolved = $fs->getDirectory($fs->input->getDirectoryPath((string) $this->reference));

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
