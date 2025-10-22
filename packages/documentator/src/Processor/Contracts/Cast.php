<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

/**
 * @template TValue of object
 */
interface Cast {
    /**
     * @return class-string<TValue>
     */
    public static function getClass(): string;

    /**
     * Returns the castable file extensions. The `*` can be used for any file.
     *
     * @return non-empty-list<string>
     */
    public static function getExtensions(): array;

    /**
     * Cast file into object.
     *
     * @param class-string<TValue> $class
     *
     * @return ?TValue
     */
    public function castTo(File $file, string $class): ?object;

    /**
     * Cast object back to string.
     *
     * @param TValue $value
     */
    public function castFrom(File $file, object $value): ?string;
}
