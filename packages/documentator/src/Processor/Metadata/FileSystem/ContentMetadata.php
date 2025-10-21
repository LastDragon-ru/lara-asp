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

    #[Override]
    public static function getClass(): string {
        return Content::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['*'];
    }

    #[Override]
    public function resolve(File $file, string $metadata): object {
        return new Content($this->adapter->read((string) $file));
    }

    #[Override]
    public function serialize(File $file, object $value): ?string {
        return $value->content;
    }
}
