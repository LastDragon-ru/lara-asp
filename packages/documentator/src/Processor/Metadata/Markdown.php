<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @implements Metadata<?Document>
 */
readonly class Markdown implements Metadata {
    public function __construct(
        protected MarkdownContract $markdown,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(File $file): mixed {
        return $file->getExtension() === 'md'
            ? $this->markdown->parse($file->getMetadata(Content::class), $file->getPath())
            : null;
    }
}
