<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @implements Cast<Document>
 */
readonly class MarkdownCast implements Cast {
    public function __construct(
        protected Markdown $markdown,
    ) {
        // empty
    }

    #[Override]
    public static function class(): string {
        return Document::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*.md';
    }

    #[Override]
    public function castTo(File $file, string $class): ?object {
        return $this->markdown->parse($file->as(Content::class)->content, $file->getPath());
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return (string) $value;
    }
}
