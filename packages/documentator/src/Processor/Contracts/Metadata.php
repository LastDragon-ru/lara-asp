<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

/**
 * @template TValue
 */
interface Metadata {
    /**
     * Resolves the metadata.
     *
     * The method should not be called directly, use {@see File::getMetadata()}.
     *
     * @return TValue
     */
    public function __invoke(File $file): mixed;
}
