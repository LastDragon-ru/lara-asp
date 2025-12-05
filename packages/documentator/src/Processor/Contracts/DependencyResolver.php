<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Traversable;

/**
 * @property-read DirectoryPath $input
 * @property-read DirectoryPath $output
 * @property-read DirectoryPath $directory
 */
interface DependencyResolver {
    /**
     * @template V of Traversable<mixed, File>|File|null
     * @template D of Dependency<V>
     *
     * @param D $dependency
     *
     * @return V
     */
    public function resolve(Dependency $dependency): Traversable|File|null;

    /**
     * @param FilePath|iterable<mixed, FilePath> $path
     */
    public function queue(FilePath|iterable $path): void;
}
