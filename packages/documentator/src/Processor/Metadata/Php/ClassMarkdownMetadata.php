<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
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

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['php'];
    }

    #[Override]
    public function isSupported(string $metadata): bool {
        return $metadata === Document::class;
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        $comment  = $file->as(ClassComment::class);
        $document = ($this->factory)($comment->comment, $file->getPath(), $comment->context);

        return $document;
    }
}
