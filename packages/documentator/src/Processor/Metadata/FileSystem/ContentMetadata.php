<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataSerializer;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @implements MetadataSerializer<Content>
 */
readonly class ContentMetadata implements MetadataSerializer {
    public function __construct() {
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
    public function isSupported(string $metadata): bool {
        return $metadata === Content::class;
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        return new Content($file->getContent());
    }

    #[Override]
    public function serialize(FilePath $path, object $value): string {
        return $value->content;
    }
}
