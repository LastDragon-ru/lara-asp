<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Composite;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\FootnotesPrefix;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\FootnotesRemove;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\GeneratedUnwrap;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\ReferencesInline;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\ReferencesPrefix;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\SelfLinksRemove;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

use function basename;
use function dirname;

class Context {
    public function __construct(
        public readonly Directory $root,
        public readonly File $file,
        public readonly string $target,
        public readonly ?string $parameters,
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
                new SelfLinksRemove(),
            ),
        );
    }

    private function getMutation(Document $document): Mutation {
        $path = $this->file->getPath();
        $path = $document->getPath() !== null
            ? Path::getPath(dirname($path), basename($document->getPath()))
            : $path;

        return new Composite(
            new Move($path),
            new GeneratedUnwrap(),
            $this->mutation,
        );
    }
}
