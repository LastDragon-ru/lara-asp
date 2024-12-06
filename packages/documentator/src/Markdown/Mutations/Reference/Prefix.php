<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset as OffsetData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference as ReferenceData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Document as DocumentNode;
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
    public function __invoke(Document $document, DocumentNode $node): iterable {
        // Just in case
        yield from [];

        // Process
        $references = $this->getReferences($node);

        foreach ($references as $reference) {
            // Changes
            $location = LocationData::get($reference);
            $text     = null;

            if ($reference instanceof Link || $reference instanceof Image) {
                $offset   = OffsetData::get($reference);
                $location = $location->withOffset($offset);
                $target   = ReferenceData::get($reference)?->getLabel();
                $target   = "{$this->prefix}-{$target}";
                $target   = Utils::escapeTextInTableCell($reference, $target);
                $text     = "[{$target}]";
            } elseif ($reference instanceof ReferenceNode) {
                $coordinate = null;

                foreach ($location as $c) {
                    $coordinate = $c;
                    break;
                }

                if ($coordinate !== null) {
                    $startLine = $coordinate->line;
                    $endLine   = $startLine;
                    $offset    = $coordinate->offset + 1;
                    $length    = mb_strlen($reference->getLabel());
                    $text      = "{$this->prefix}-{$reference->getLabel()}";
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
     * @return list<AbstractWebResource|ReferenceNode>
     */
    protected function getReferences(DocumentNode $node): array {
        $references = [];

        foreach ($node->iterator() as $child) {
            if ($child instanceof AbstractWebResource && ReferenceData::get($child) !== null) {
                $references[] = $child;
            } elseif ($child instanceof ReferenceNode) {
                $references[] = $child;
            } else {
                // empty
            }
        }

        return $references;
    }
}
