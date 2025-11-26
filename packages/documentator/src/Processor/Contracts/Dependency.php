<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Traversable;

/**
 * Task dependency (= another file or directory).
 *
 * @template TValue of Traversable<mixed, File>|File|null
 */
interface Dependency {
    /**
     * @throws DependencyUnresolvable
     *
     * @return TValue
     */
    public function __invoke(FileSystem $fs): Traversable|File|null;

    /**
     * Relative path will be resolved based on {@see FileSystem::$input}.
     */
    public function getPath(FileSystem $fs): DirectoryPath|File|FilePath;
}
