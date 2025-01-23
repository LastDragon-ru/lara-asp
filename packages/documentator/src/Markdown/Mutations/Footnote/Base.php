<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Extension\Footnote\Node\FootnoteRef;

abstract readonly class Base {
    public function __construct() {
        // empty
    }

    /**
     * @return iterable<mixed, Footnote|FootnoteRef>
     */
    protected function nodes(Document $document): iterable {
        // Just in case
        yield from [];

        // Search
        foreach ($document->node->iterator() as $node) {
            if ($node instanceof FootnoteRef || $node instanceof Footnote) {
                yield $node;
            }
        }
    }
}
