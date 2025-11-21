<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

interface Adapter {
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

    public function read(FilePath $path): string;

    public function write(FilePath $path, string $content): void;

    public function reset(): void;
}
