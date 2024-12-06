<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Composite;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote\Prefix as FootnotesPrefix;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote\Remove as FootnotesRemove;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated\Unwrap;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link\RemoveToSelf;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference\Inline as ReferencesInline;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference\Prefix as ReferencesPrefix;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

class Context {
    public function __construct(
        public readonly Directory $root,
        public readonly File $file,
        public readonly Document $document,
        public readonly Block $node,
        private readonly Mutation $mutation,
    ) {
        // empty
    }

    /**
     * Renames all references/footnotes/etc to make possible inline the
     * document into another document without conflicts/ambiguities.
     */
    public function toInlinable(Document $document): Document {
        $seed = Utils::getSeed($this, $document);

        return $document->mutate(
            $this->getMutation($document),
            new Composite(
                new FootnotesPrefix($seed),
                new ReferencesPrefix($seed),
            ),
        );
    }

    /**
     * Inlines all references, removes footnotes, etc, to make possible
     * extract any block/paragraph from the document without losing
     * information.
     */
    public function toSplittable(Document $document): Document {
        return $document->mutate(
            $this->getMutation($document),
            new Composite(
                new FootnotesRemove(),
                new ReferencesInline(),
                new RemoveToSelf(),
            ),
        );
    }

    private function getMutation(Document $document): Mutation {
        $path = $this->file->getPath();
        $path = $path->getPath(new FilePath($document->getPath()?->getName() ?? ''));

        return new Composite(
            new Move($path),
            new Unwrap(),
            $this->mutation,
        );
    }
}
