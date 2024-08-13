<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
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
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): array {
        $changes = [];

        foreach ($node->iterator() as $child) {
            $location = match (true) {
                $child instanceof FootnoteRef, $child instanceof Footnote => Utils::getLocation($child),
                default                                                   => null,
            };

            if ($location) {
                $changes[] = [$location, null];
            }
        }

        return $changes;
    }
}
