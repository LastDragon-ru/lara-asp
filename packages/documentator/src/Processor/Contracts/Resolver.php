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
 * + `~input/` - relative to {@see self::$input}
 * + `~output/` - relative to {@see self::$output}
 * + Other - as is.
 *
 * @property-read DirectoryPath $input
 * @property-read DirectoryPath $output
 * @property-read DirectoryPath $directory
 *
 * @phpstan-require-extends ResolverImpl
 */
interface Resolver {
    /**
     * @param FilePath|non-empty-string $path
     */
    public function get(FilePath|string $path): File;

    /**
     * @param FilePath|non-empty-string $path
     */
    public function find(FilePath|string $path): ?File;

    /**
     * Converts the file into the object. The result will be cached until the
     * file is changed.
     *
     * @template T of object
     *
     * @param File|FilePath|non-empty-string $path
     * @param class-string<Cast<T>>          $cast
     *
     * @return T
     */
    public function cast(File|FilePath|string $path, string $cast): object;

    /**
     * If the file exists, it will be overwritten.
     *
     * @param File|FilePath|non-empty-string $path
     */
    public function save(File|FilePath|string $path, string $content): void;

    /**
     * The file(s) will be processed after the current file (in undefined order).
     *
     * @param FilePath|iterable<mixed, FilePath|non-empty-string>|non-empty-string $path
     */
    public function queue(FilePath|iterable|string $path): void;

    /**
     * @param list<non-empty-string>|non-empty-string $include Glob(s) to include.
     * @param list<non-empty-string>|non-empty-string $exclude Glob(s) to exclude.
     * @param DirectoryPath|non-empty-string|null     $directory
     *
     * @return iterable<mixed, FilePath>
     */
    public function search(
        array|string $include,
        array|string $exclude,
        DirectoryPath|string|null $directory = null,
    ): iterable;
}
