<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use Override;

/**
 * @implements MetadataResolver<Document>
 */
readonly class MarkdownMetadata implements MetadataResolver {
    public function __construct(
        protected Markdown $markdown,
    ) {
        // empty
    }

    #[Override]
    public static function getClass(): string {
        return Document::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['md'];
    }

    #[Override]
    public function resolve(File $file, string $metadata): ?object {
        return $this->markdown->parse($file->as(Content::class)->content, $file->getPath());
    }

    #[Override]
    public function serialize(File $file, object $value): ?string {
        return (string) $value;
    }
}
