<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;
use Traversable;

/**
 * @template TValue of Traversable<mixed, Directory|File>|Directory|File|null
 *
 * @implements Dependency<TValue|null>
 */
readonly class Optional implements Dependency {
    public function __construct(
        /**
         * @var Dependency<TValue>
         */
        protected Dependency $dependency,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): mixed {
        $resolved = null;

        try {
            $resolved = ($this->dependency)($fs);
        } catch (DependencyUnresolvable) {
            $resolved = null;
        }

        return $resolved;
    }

    #[Override]
    public function getPath(): DirectoryPath|FilePath {
        return $this->dependency->getPath();
    }
}
