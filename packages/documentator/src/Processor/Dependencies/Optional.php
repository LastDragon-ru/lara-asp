<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use Traversable;

/**
 * @template TValue of Traversable<mixed, File>|File
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

    /**
     * @return TValue|null
     */
    #[Override]
    public function __invoke(FileSystem $fs): Traversable|File|null {
        $resolved = null;

        try {
            $resolved = ($this->dependency)($fs);
        } catch (DependencyUnresolvable) {
            $resolved = null;
        }

        return $resolved;
    }

    #[Override]
    public function getPath(FileSystem $fs): DirectoryPath|FilePath {
        return $this->dependency->getPath($fs);
    }
}
