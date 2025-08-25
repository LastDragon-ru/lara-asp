<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

interface FileSystemAdapter {
    public function isFile(string $path): bool;

    public function isDirectory(string $path): bool;

    /**
     * @param list<string> $exclude
     * @param list<string> $include
     *
     * @return iterable<array-key, string>
     */
    public function getFilesIterator(
        string $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): iterable;

    /**
     * @param list<string> $exclude
     * @param list<string> $include
     *
     * @return iterable<array-key, string>
     */
    public function getDirectoriesIterator(
        string $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): iterable;

    public function read(string $path): string;

    public function write(string $path, string $content): void;
}
