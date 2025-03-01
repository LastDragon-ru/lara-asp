<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Traversable;

/**
 * Task dependency (= another file or directory).
 *
 * @template TValue of Traversable<mixed, Directory|File>|Directory|File|null
 */
interface Dependency {
    /**
     * @throws DependencyUnresolvable
     *
     * @return TValue
     */
    public function __invoke(FileSystem $fs): Traversable|Directory|File|null;

    /**
     * Used only for events. Relative path will be resolved based on {@see FileSystem::$input}.
     */
    public function getPath(FileSystem $fs): Directory|DirectoryPath|File|FilePath;
}
