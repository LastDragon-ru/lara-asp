<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation as MutationContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

abstract readonly class Base implements MutationContract {
    public function __construct() {
        // empty
    }

    /**
     * @return iterable<mixed, Link>
     */
    protected function nodes(Document $document): iterable {
        // Just in case
        yield from [];

        // Search
        foreach ($document->node->iterator() as $node) {
            if ($node instanceof Link) {
                yield $node;
            }
        }
    }
}
