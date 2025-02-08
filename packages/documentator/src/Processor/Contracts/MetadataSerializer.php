<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Core\Path\FilePath;

/**
 * @template TValue of object
 *
 * @extends MetadataResolver<TValue>
 */
interface MetadataSerializer extends MetadataResolver {
    /**
     * Serialize metadata back to the string.
     *
     * @param TValue $value
     */
    public function serialize(FilePath $path, object $value): string;
}
