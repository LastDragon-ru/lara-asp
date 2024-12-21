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
 * Task dependency (= another file).
 *
 * @template TValue of Traversable<mixed, Directory|File>|Directory|File|null
 */
interface Dependency {
    /**
     * @throws DependencyUnresolvable
     *
     * @return TValue
     */
    public function __invoke(FileSystem $fs): mixed;

    public function getPath(): DirectoryPath|FilePath;
}
