<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

interface Adapter {
    public function isFile(string $path): bool;

    public function isDirectory(string $path): bool;

    /**
     * @param array<array-key, string>|string|null $exclude
     * @param array<array-key, string>|string|null $include
     *
     * @return iterable<array-key, string>
     */
    public function getFilesIterator(
        string $directory,
        array|string|null $include = null,
        array|string|null $exclude = null,
        ?int $depth = null,
    ): iterable;

    /**
     * @param array<array-key, string>|string|null $exclude
     * @param array<array-key, string>|string|null $include
     *
     * @return iterable<array-key, string>
     */
    public function getDirectoriesIterator(
        string $directory,
        array|string|null $include = null,
        array|string|null $exclude = null,
        ?int $depth = null,
    ): iterable;

    public function read(string $path): string;

    public function write(string $path, string $content): void;
}
