<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\NodeIterator;

abstract readonly class Base {
    protected const int MaxLevel = 6;

    public function __construct() {
        // empty
    }

    /**
     * @return iterable<mixed, Heading>
     */
    protected function nodes(Document $document): iterable {
        // Just in case
        yield from [];

        // Search
        foreach ($document->node->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if ($node instanceof Heading) {
                yield $node;
            }
        }
    }
}
