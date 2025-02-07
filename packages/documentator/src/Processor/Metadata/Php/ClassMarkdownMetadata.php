<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
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
        // Comment?
        $comment = $file->getMetadata(PhpClassComment::class);

        if ($comment === null) {
            throw new Exception('Class not found.');
        }

        // Parse
        $document = ($this->factory)($comment->comment, $file->getPath(), $comment->context);

        return $document;
    }
}
