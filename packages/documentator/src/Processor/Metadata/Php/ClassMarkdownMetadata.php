<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use Override;

/**
 * @implements MetadataResolver<Document>
 */
readonly class ClassMarkdownMetadata implements MetadataResolver {
    public function __construct(
        protected PhpDocumentFactory $factory,
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
        return ['php'];
    }

    #[Override]
    public function resolve(File $file, string $metadata): object {
        $comment  = $file->as(ClassComment::class);
        $document = ($this->factory)($comment->comment, $file->getPath(), $comment->context);

        return $document;
    }

    #[Override]
    public function serialize(File $file, object $value): ?string {
        return null;
    }
}
