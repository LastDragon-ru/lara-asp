<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;

interface FileSystemAdapter {
    public function isFile(FilePath $path): bool;

    public function isDirectory(DirectoryPath $path): bool;

    /**
     * @param list<string> $exclude
     * @param list<string> $include
     *
     * @return iterable<array-key, FilePath>
     */
    public function getFilesIterator(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): iterable;

    /**
     * @param list<string> $exclude
     * @param list<string> $include
     *
     * @return iterable<array-key, DirectoryPath>
     */
    public function getDirectoriesIterator(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): iterable;

    public function read(FilePath $path): string;

    public function write(FilePath $path, string $content): void;
}
