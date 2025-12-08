<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

/**
 * @template TValue of object
 */
interface Cast {
    /**
     * @return class-string<TValue>
     */
    public static function class(): string;

    /**
     * Glob pattern(s) to define castable filenames. It will be matched against
     * the name of the file and thus cannot contain the `/`.
     *
     * @return non-empty-list<non-empty-string>|non-empty-string
     */
    public static function glob(): array|string;

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
