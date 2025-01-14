<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use Iterator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation as MutationContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;

abstract readonly class Mutation implements MutationContract {
    public function __construct() {
        // empty
    }

    /**
     * @return Iterator<array-key, AbstractWebResource|ReferenceNode>
     */
    protected function nodes(Document $document): Iterator {
        // Just in case
        yield from [];

        // Search
        foreach ($document->node->iterator() as $node) {
            if ($node instanceof AbstractWebResource && Reference::get($node) !== null) {
                yield $node;
            } elseif ($node instanceof ReferenceNode) {
                yield $node;
            } else {
                // empty
            }
        }
    }
}
