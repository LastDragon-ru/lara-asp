<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;

use function mb_strlen;
use function mb_strpos;
use function mb_substr;
use function min;
use function str_repeat;
use function str_starts_with;
use function trim;

/**
 * Updates all ATX headings levels.
 */
class HeadingsLevel implements Mutation {
    public function __construct(
        /**
         * @var int<1, 6>
         */
        protected int $startLevel,
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
        $highest  = 6;
        $headings = $this->getHeadings($document, $node, $highest);
        $diff     = $this->startLevel - $highest;

        if ($diff === 0) {
            return;
        }

        foreach ($headings as [$heading, $location, $text]) {
            $level  = min(6, $heading->getLevel() + $diff);
            $prefix = mb_substr($text, 0, (int) mb_strpos($text, '#'));
            $eols   = mb_strlen($text) - mb_strlen(trim($text, "\n"));
            $text   = mb_substr($text, mb_strlen($prefix));
            $text   = $prefix.str_repeat('#', $level).' '.trim(trim($text, '#')).str_repeat("\n", $eols);

            yield [$location, $text];
        }
    }

    /**
     * @return list<array{Heading, Location, string}>
     */
    private function getHeadings(Document $document, DocumentNode $node, int &$highest): array {
        $headings = [];

        foreach ($node->iterator() as $child) {
            // Heading?
            if (!($child instanceof Heading)) {
                continue;
            }

            // Location?
            $location = LocationData::get($child);

            if ($location === null) {
                continue;
            }

            // ATX?
            $line = $document->getText($location);

            if ($line === null || !str_starts_with(trim($line), '#')) {
                continue;
            }

            // Ok
            $headings[] = [$child, $location, $line];
            $highest    = min($highest, $child->getLevel());
        }

        return $headings;
    }
}
