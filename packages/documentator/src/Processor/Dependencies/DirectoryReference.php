<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

use function is_string;

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
        try {
            return $fs->getDirectory($this->reference);
        } catch (DirectoryNotFound $exception) {
            throw new DependencyUnresolvable($this, $exception);
        }
    }

    #[Override]
    public function getPath(): DirectoryPath {
        return match (true) {
            is_string($this->reference) => new DirectoryPath($this->reference),
            default                     => $this->reference,
        };
    }
}
