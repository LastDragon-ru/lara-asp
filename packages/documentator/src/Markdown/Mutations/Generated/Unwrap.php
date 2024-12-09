<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Block;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\EndMarkerLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\StartMarkerLocation;
use League\CommonMark\Node\NodeIterator;
use Override;

/**
 * Removes start and end marks of Generated block.
 */
readonly class Unwrap implements Mutation {
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
        foreach ($document->node->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            // Generated?
            if (!($node instanceof Block)) {
                continue;
            }

            // Start?
            $startMarker = StartMarkerLocation::optional()->get($node);

            if ($startMarker !== null) {
                yield [$startMarker, null];
            }

            // End
            $endMarker = EndMarkerLocation::optional()->get($node);

            if ($endMarker !== null) {
                yield [$endMarker, null];
            }
        }
    }
}
