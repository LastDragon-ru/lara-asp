<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileCast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Override;

/**
 * @implements FileCast<SerializedFile>
 */
readonly class Serialized implements FileCast {
    public function __construct(
        protected Serializer $serializer,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(Resolver $resolver, File $file): object {
        return new SerializedFile($this->serializer, $file);
    }
}
