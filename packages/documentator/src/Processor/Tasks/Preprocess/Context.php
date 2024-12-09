<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Block;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Composite;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\MakeInlinable;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\MakeSplittable;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated\Unwrap;
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
     * Moves the document into the correct location and makes it inlinable.
     *
     * @see MakeInlinable
     */
    public function toInlinable(Document $document): Document {
        $seed = Utils::getSeed($this, $document);

        return $document->mutate(
            $this->getMutation($document),
            new MakeInlinable($seed),
        );
    }

    /**
     * Moves the document into the correct location and makes it splittable.
     *
     * @see MakeSplittable
     */
    public function toSplittable(Document $document): Document {
        return $document->mutate(
            $this->getMutation($document),
            new MakeSplittable(),
        );
    }

    private function getMutation(Document $document): Mutation {
        $path = $this->file->getPath();
        $path = $path->getPath(new FilePath($document->path?->getName() ?? ''));

        return new Composite(
            new Move($path),
            new Unwrap(),
            $this->mutation,
        );
    }
}
