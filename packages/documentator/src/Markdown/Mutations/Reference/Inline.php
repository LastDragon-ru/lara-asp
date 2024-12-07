<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use Iterator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;

use function rawurldecode;

/**
 * Inlines all references.
 */
class Inline implements Mutation {
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

        // Process
        foreach ($this->nodes($node) as $reference) {
            // Change
            $location = Location::get($reference);
            $text     = null;

            if ($reference instanceof Link || $reference instanceof Image) {
                $offset   = Offset::get($reference);
                $location = $location->withOffset($offset);
                $title    = Utils::getLinkTitle($reference, (string) $reference->getTitle());
                $target   = Utils::getLinkTarget($reference, rawurldecode($reference->getUrl()));
                $text     = $title !== '' ? "({$target} {$title})" : "({$target})";
            } elseif ($reference instanceof ReferenceNode) {
                $text = '';
            } else {
                // skipped
            }

            if ($text !== null) {
                yield [$location, $text !== '' ? $text : null];
            }
        }
    }

    /**
     * @return Iterator<array-key, AbstractWebResource|ReferenceNode>
     */
    private function nodes(DocumentNode $node): Iterator {
        // Just in case
        yield from [];

        // Search
        foreach ($node->iterator() as $child) {
            if ($child instanceof AbstractWebResource && Reference::get($child) !== null) {
                yield $child;
            } elseif ($child instanceof ReferenceNode) {
                yield $child;
            } else {
                // empty
            }
        }
    }
}
