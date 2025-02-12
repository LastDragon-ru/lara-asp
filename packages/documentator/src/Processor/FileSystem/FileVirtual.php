<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnresolvable;
use Override;

class FileVirtual extends File {
    /**
     * @template T of object
     *
     * @param class-string<T> $metadata
     *
     * @return T
     */
    #[Override]
    public function as(string $metadata): object {
        throw new MetadataUnresolvable($this, $metadata);
    }
}
