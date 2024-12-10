<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use Iterator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
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
    public function __invoke(Document $document): iterable {
        // Just in case
        yield from [];

        // Process
        foreach ($this->nodes($document) as $node) {
            // Change
            $location = Location::get($node);
            $text     = null;

            if ($node instanceof Link || $node instanceof Image) {
                $offset   = Offset::get($node);
                $location = $location->withOffset($offset);
                $title    = Utils::getLinkTitle($node, (string) $node->getTitle());
                $target   = Utils::getLinkTarget($node, rawurldecode($node->getUrl()));
                $text     = $title !== '' ? "({$target} {$title})" : "({$target})";
            } elseif ($node instanceof ReferenceNode) {
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
    private function nodes(Document $document): Iterator {
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
