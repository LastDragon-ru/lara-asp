<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use Override;

use function is_a;

/**
 * @implements MetadataResolver<Document>
 */
readonly class MarkdownMetadata implements MetadataResolver {
    public function __construct(
        protected Markdown $markdown,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['md'];
    }

    #[Override]
    public function isSupported(FilePath $path, string $metadata): bool {
        return $path->getExtension() === 'md' && is_a($metadata, Document::class, true);
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        return $this->markdown->parse($file->as(Content::class)->content, $file->getPath());
    }

    #[Override]
    public function serialize(FilePath $path, object $value): ?string {
        return (string) $value;
    }
}
