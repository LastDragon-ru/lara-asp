<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use Iterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

/**
 * @template TValue of Iterator<mixed, Directory|File>|Directory|File|null
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
    public function __invoke(FileSystem $fs, Directory $root, File $file): mixed {
        $resolved = null;

        try {
            $resolved = ($this->dependency)($fs, $root, $file);
        } catch (DependencyNotFound) {
            $resolved = null;
        }

        return $resolved;
    }

    #[Override]
    public function __toString(): string {
        return (string) $this->dependency;
    }
}
