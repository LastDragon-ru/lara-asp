<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;

use function mb_strlen;
use function mb_substr;

/**
 * Adds unique prefix for all footnotes.
 */
readonly class FootnotesPrefix implements Mutation {
    public function __construct(
        protected string $prefix,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): array {
        $changes = [];

        foreach ($node->iterator() as $child) {
            // Footnote?
            if (!($child instanceof FootnoteRef) && !($child instanceof Footnote)) {
                continue;
            }

            // Replace
            $label    = $this->getLabel($document, $child);
            $location = $label ? $this->getLabelLocation($child, $label) : null;

            if ($location) {
                $changes[] = [$location, "{$this->prefix}-{$label}"];
            }
        }

        return $changes;
    }

    private function getLabel(Document $document, Footnote|FootnoteRef $footnote): ?string {
        // The thephpleague/commonmark replaces the original title of
        // `FootnoteRef` to make it unique. We need to find original.
        $label = $footnote->getReference()->getLabel();

        if ($footnote instanceof FootnoteRef) {
            $location = Utils::getLocation($footnote);
            $label    = $location
                ? (mb_substr($document->getText($location) ?? '', 2, -1) ?: '')
                : null;
        }

        return $label;
    }

    private function getLabelLocation(Footnote|FootnoteRef $footnote, string $label): ?Location {
        // Get the start line
        $location   = Utils::getLocation($footnote);
        $coordinate = null;

        foreach ($location ?? [] as $c) {
            $coordinate = $c;
            break;
        }

        if ($coordinate === null) {
            return null;
        }

        // Location
        $startLine = $coordinate->line;
        $endLine   = $startLine;
        $offset    = $coordinate->offset + 2;
        $length    = mb_strlen($label);

        return new Location($startLine, $endLine, $offset, $length);
    }
}
