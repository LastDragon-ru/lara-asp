<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Content;
use Override;

/**
 * @implements MetadataResolver<Document>
 */
readonly class MarkdownMetadata implements MetadataResolver {
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
        return $this->markdown->parse($file->getMetadata(Content::class), $file->getPath());
    }
}
