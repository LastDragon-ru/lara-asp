<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

interface Adapter {
    public function exists(DirectoryPath|FilePath $path): bool;

    /**
     * @param list<non-empty-string> $exclude globs
     * @param list<non-empty-string> $include globs
     * @param ?int<0, max>           $depth
     *
     * @return iterable<mixed, FilePath>
     */
    public function search(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): iterable;

    public function read(FilePath $path): string;

    public function write(FilePath $path, string $content): void;

    public function reset(): void;
}
