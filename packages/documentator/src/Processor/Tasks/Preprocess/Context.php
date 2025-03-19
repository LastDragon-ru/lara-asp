<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\MakeInlinable;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\MakeSplittable;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated\Unwrap;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use League\CommonMark\Node\Node;

readonly class Context {
    public function __construct(
        public File $file,
        public Document $document,
        public ReferenceNode $node,
        /**
         * @var Mutation<covariant Node>
         */
        private Mutation $mutation,
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

        return $document
            ->mutate($this->getMutation($document))
            ->mutate(new MakeInlinable($seed));
    }

    /**
     * Moves the document into the correct location and makes it splittable.
     *
     * @see MakeSplittable
     */
    public function toSplittable(Document $document): Document {
        return $document
            ->mutate($this->getMutation($document))
            ->mutate(new MakeSplittable());
    }

    /**
     * @return array<array-key, Mutation<covariant Node>>
     */
    private function getMutation(Document $document): array {
        $path = $this->file->getFilePath($document->path?->getName() ?? '');

        return [
            new Move($path),
            new Unwrap(),
            $this->mutation,
        ];
    }
}
