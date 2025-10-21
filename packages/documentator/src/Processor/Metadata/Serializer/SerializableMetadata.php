<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Serializer;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Override;

/**
 * @implements MetadataResolver<Serializable>
 */
readonly class SerializableMetadata implements MetadataResolver {
    public function __construct(
        protected Serializer $serializer,
    ) {
        // empty
    }

    #[Override]
    public static function getClass(): string {
        return Serializable::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['json'];
    }

    #[Override]
    public function resolve(File $file, string $metadata): ?object {
        return $this->serializer->deserialize($metadata, $file->as(Content::class)->content, $file->getExtension());
    }

    #[Override]
    public function serialize(File $file, object $value): ?string {
        return $this->serializer->serialize($value, $file->getExtension());
    }
}
