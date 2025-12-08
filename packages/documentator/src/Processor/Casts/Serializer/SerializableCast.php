<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Serializer;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Override;

/**
 * @implements Cast<Serializable>
 */
readonly class SerializableCast implements Cast {
    public function __construct(
        protected Serializer $serializer,
    ) {
        // empty
    }

    #[Override]
    public static function class(): string {
        return Serializable::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*.json';
    }

    #[Override]
    public function castTo(File $file, string $class): ?object {
        return $this->serializer->deserialize($class, $file->content, $file->extension);
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return $this->serializer->serialize($value, $file->extension);
    }
}
