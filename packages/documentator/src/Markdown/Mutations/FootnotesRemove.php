<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;

/**
 * Removes all footnotes.
 */
readonly class FootnotesRemove implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @return iterable<array-key, array{Location, ?string}>
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): iterable {
        // Just in case
        yield from [];

        // Process
        foreach ($node->iterator() as $child) {
            $location = match (true) {
                $child instanceof FootnoteRef, $child instanceof Footnote => LocationData::get($child),
                default                                                   => null,
            };

            if ($location !== null) {
                yield [$location, null];
            }
        }
    }
}
