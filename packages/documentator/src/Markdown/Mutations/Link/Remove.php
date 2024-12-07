<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use Iterator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Document as DocumentNode;
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
    public function __invoke(Document $document, DocumentNode $node): iterable {
        // Just in case
        yield from [];

        // Update
        foreach ($this->nodes($document, $node) as $link) {
            $location = Location::get($link);
            $offset   = Offset::get($link);

            yield [$location->withLength(1), null];           // [
            yield [$location->withOffset($offset - 1), null]; // ](...)
        }

        // Return
        return true;
    }

    /**
     * @return Iterator<array-key, Link>
     */
    private function nodes(Document $document, DocumentNode $node): Iterator {
        // Just in case
        yield from [];

        // Search
        foreach ($node->iterator() as $child) {
            if ($child instanceof Link && $this->isLink($document, $child)) {
                yield $child;
            }
        }
    }

    protected function isLink(Document $document, Link $node): bool {
        return true;
    }
}
