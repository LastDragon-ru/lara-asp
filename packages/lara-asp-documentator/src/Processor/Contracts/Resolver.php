<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver as ResolverImpl;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

/**
 * Resolves task dependencies. The dependency will be processed before returning.
 *
 * Paths solution:
 *
 * + Relative - relative to {@see self::$directory}.
 * + Other - as is.
 *
 * @property-read DirectoryPath $input
 * @property-read DirectoryPath $output
 * @property-read DirectoryPath $directory
 *
 * @phpstan-require-extends ResolverImpl
 */
interface Resolver {
    public function get(FilePath $path): File;

    public function find(FilePath $path): ?File;

    /**
     * Converts the file into the object. The result will be cached until the
     * file is changed.
     *
     * @template T of object
     *
     * @param class-string<Cast<T>> $cast
     *
     * @return T
     */
    public function cast(File|FilePath $path, string $cast): object;

    /**
     * If the file exists, it will be overwritten.
     */
    public function save(File|FilePath $path, string $content): void;

    /**
     * The file(s) will be processed after the current file (in undefined order).
     *
     * @param FilePath|iterable<mixed, FilePath> $path
     */
    public function queue(FilePath|iterable $path): void;

    /**
     * @param DirectoryPath|FilePath|File|iterable<mixed, DirectoryPath|FilePath> $path
     */
    public function delete(DirectoryPath|FilePath|File|iterable $path): void;

    /**
     * @param list<non-empty-string>|non-empty-string $include Glob(s) to include.
     * @param list<non-empty-string>|non-empty-string $exclude Glob(s) to exclude.
     *
     * @return iterable<mixed, DirectoryPath|FilePath>
     */
    public function search(
        ?DirectoryPath $directory = null,
        array|string $include = [],
        array|string $exclude = [],
        bool $hidden = false,
    ): iterable;
}
