<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use Iterator;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content as ContentData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference as ReferenceData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use Override;

use function mb_strlen;

/**
 * Adds unique prefix for all references.
 */
class Prefix implements Mutation {
    public function __construct(
        protected string $prefix,
    ) {
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
            // Changes
            $location = LocationData::get($node);
            $text     = null;

            if ($node instanceof Link || $node instanceof Image) {
                $content  = ContentData::get($node);
                $location = $location->withOffset(($content->offset - $location->offset) + (int) $content->length + 1);
                $target   = ReferenceData::get($node)?->getLabel();
                $target   = "{$this->prefix}-{$target}";
                $target   = Utils::escapeTextInTableCell($node, $target);
                $text     = "[{$target}]";
            } elseif ($node instanceof ReferenceNode) {
                $coordinate = null;

                foreach ($location as $c) {
                    $coordinate = $c;
                    break;
                }

                if ($coordinate !== null) {
                    $startLine = $coordinate->line;
                    $endLine   = $startLine;
                    $offset    = $coordinate->offset + 1;
                    $length    = mb_strlen($node->getLabel());
                    $text      = "{$this->prefix}-{$node->getLabel()}";
                    $location  = new Location($startLine, $endLine, $offset, $length);
                }
            } else {
                // skipped
            }

            if ($text !== null) {
                yield [$location, $text];
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
            if ($node instanceof AbstractWebResource && ReferenceData::get($node) !== null) {
                yield $node;
            } elseif ($node instanceof ReferenceNode) {
                yield $node;
            } else {
                // empty
            }
        }
    }
}
