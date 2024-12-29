<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;

interface MetadataResolver {
    /**
     * @template T
     *
     * @param class-string<Metadata<T>> $metadata
     *
     * @return T
     */
    public function get(File $file, string $metadata): mixed;
}
