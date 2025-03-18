<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use League\CommonMark\Node\Node;
use Override;

use function mb_strlen;
use function mb_substr;

/**
 * Adds unique prefix for all footnotes.
 */
readonly class Prefix extends Base {
    public function __construct(
        protected string $prefix,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        $label    = $this->getLabel($document, $node);
        $location = $this->getLabelLocation($node, $label);
        $mutagens = $location !== null
            ? [new Replace($location, "{$this->prefix}-{$label}")]
            : [];

        return $mutagens;
    }

    private function getLabel(Document $document, Footnote|FootnoteRef $footnote): string {
        // The thephpleague/commonmark replaces the original title of
        // `FootnoteRef` to make it unique. We need to find original.
        $label = $footnote->getReference()->getLabel();

        if ($footnote instanceof FootnoteRef) {
            $location = LocationData::get($footnote);
            $label    = mb_substr($document->getText($location), 2, -1);
        }

        return $label;
    }

    private function getLabelLocation(Footnote|FootnoteRef $footnote, string $label): ?Location {
        // Get the start line
        $location   = LocationData::get($footnote);
        $coordinate = null;

        foreach ($location as $c) {
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
