<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

/**
 * @template TValue of object
 */
interface MetadataResolver {
    /**
     * Returns the file extensions which the resolver can use to create metadata.
     * The `*` can be used for any file.
     *
     * @return non-empty-list<string>
     */
    public static function getExtensions(): array;

    /**
     * @phpstan-assert-if-true class-string<TValue> $metadata
     *
     * @param class-string $metadata
     */
    public function isSupported(string $metadata): bool;

    /**
     * Resolves the metadata.
     *
     * @param class-string<TValue> $metadata
     *
     * @return TValue
     */
    public function resolve(File $file, string $metadata): mixed;
}
