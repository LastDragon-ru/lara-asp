<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @implements MetadataResolver<Content>
 */
readonly class ContentMetadata implements MetadataResolver {
    public function __construct(
        protected FileSystemAdapter $adapter,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['*'];
    }

    #[Override]
    public function isSupported(File $file, string $metadata): bool {
        return $metadata === Content::class;
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        return new Content($this->adapter->read((string) $file));
    }

    #[Override]
    public function serialize(File $file, object $value): ?string {
        return $value->content;
    }
}
