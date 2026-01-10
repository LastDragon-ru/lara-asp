<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use Override;
use WeakMap;

/**
 * Removes unused references.
 *
 * @implements Mutation<DocumentNode>
 */
readonly class RemoveUnused implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            DocumentNode::class,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        // Find (un)used references (is it possible to optimize?)
        $references = [];
        $used       = new WeakMap();

        foreach ($node->iterator() as $child) {
            if ($child instanceof ReferenceNode) {
                $references[] = $child;
            } elseif ($child instanceof AbstractWebResource) {
                $reference = Reference::get($child);

                if ($reference !== null) {
                    $used[$reference] = true;
                }
            } else {
                // empty
            }
        }

        // Mutate
        $mutagens = [];

        foreach ($references as $reference) {
            $origin = $reference->getReference();

            if ($origin !== null && !isset($used[$origin])) {
                $mutagens[] = new Delete(Location::get($reference));
            }
        }

        return $mutagens;
    }
}
