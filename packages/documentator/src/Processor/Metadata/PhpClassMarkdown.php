<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use Override;

/**
 * @implements Metadata<?Document>
 */
readonly class PhpClassMarkdown implements Metadata {
    public function __construct(
        protected PhpDocumentFactory $factory,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(File $file): mixed {
        // Comment?
        $comment = $file->getMetadata(PhpClassComment::class);

        if ($comment === null) {
            return null;
        }

        // Parse
        $document = ($this->factory)($comment->comment, $file->getPath(), $comment->context);

        return $document;
    }
}
