<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

use function file_get_contents;

/**
 * @implements MetadataResolver<Content>
 */
readonly class ContentMetadata implements MetadataResolver {
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
        return new Content((string) file_get_contents((string) $file));
    }
}
