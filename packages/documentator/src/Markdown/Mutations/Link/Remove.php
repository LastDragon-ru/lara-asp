<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use Iterator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use Override;

/**
 * Removes all links.
 */
readonly class Remove implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        // Just in case
        yield from [];

        // Update
        foreach ($this->nodes($document) as $node) {
            $location = Location::get($node);
            $content  = Content::get($node);

            yield [$location->withLength(1), null];                                                               // [
            yield [$location->withOffset(($content->offset - $location->offset) + (int) $content->length), null]; // ](...)
        }

        // Return
        return true;
    }

    /**
     * @return Iterator<array-key, Link>
     */
    private function nodes(Document $document): Iterator {
        // Just in case
        yield from [];

        // Search
        foreach ($document->node->iterator() as $node) {
            if ($node instanceof Link && $this->isLink($document, $node)) {
                yield $node;
            }
        }
    }

    protected function isLink(Document $document, Link $node): bool {
        return true;
    }
}
