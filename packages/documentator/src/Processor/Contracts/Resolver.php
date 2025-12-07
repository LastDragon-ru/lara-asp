<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver as ResolverImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

/**
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
     * If the file exists, it will be overwritten.
     *
     * @param File|FilePath|non-empty-string $path
     */
    public function save(File|FilePath|string $path, object|string $content): File;

    /**
     * @param FilePath|iterable<mixed, FilePath|non-empty-string>|non-empty-string $path
     */
    public function queue(FilePath|iterable|string $path): void;

    /**
     * @param list<non-empty-string>|non-empty-string $include Glob(s) to include.
     * @param list<non-empty-string>|non-empty-string $exclude Glob(s) to exclude.
     * @param ?int<0, max>                            $depth   Maximum depth.
     * @param DirectoryPath|non-empty-string|null     $directory
     *
     * @return iterable<mixed, FilePath>
     */
    public function search(
        array|string $include,
        array|string $exclude,
        ?int $depth,
        DirectoryPath|string|null $directory = null,
    ): iterable;
}
