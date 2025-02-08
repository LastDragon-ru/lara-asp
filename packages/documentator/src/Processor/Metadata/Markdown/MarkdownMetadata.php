<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataSerializer;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use Override;

/**
 * @implements MetadataSerializer<Document>
 */
readonly class MarkdownMetadata implements MetadataSerializer {
    public function __construct(
        protected MarkdownContract $markdown,
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
    public function isSupported(string $metadata): bool {
        return $metadata === Document::class;
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        return $this->markdown->parse($file->as(Content::class)->content, $file->getPath());
    }

    #[Override]
    public function serialize(FilePath $path, object $value): string {
        return (string) $value;
    }
}
