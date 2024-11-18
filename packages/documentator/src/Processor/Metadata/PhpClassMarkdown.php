<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use Override;

/**
 * @implements Metadata<?Document>
 */
class PhpClassMarkdown implements Metadata {
    public function __construct(
        protected readonly LinkFactory $factory,
        protected readonly PhpClassComment $comment,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(File $file): mixed {
        // Comment?
        $comment = $file->getMetadata($this->comment);

        if ($comment === null) {
            return null;
        }

        // Parse
        $document = $comment->comment->getDocument($this->factory, $comment->context, $file->getPath());

        return $document;
    }
}
