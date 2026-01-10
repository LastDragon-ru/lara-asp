<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Contracts;

use LastDragon_ru\LaraASP\Serializer\Normalizers\SerializableNormalizer;

/**
 * Marks that {@see Serializable} is partial (=serialized data may have extra
 * properties which are not mapped into class properties and will be skipped).
 *
 * @see Serializable
 * @see SerializableNormalizer
 */
interface Partial {
    // empty
}
