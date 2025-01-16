<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use Override;

use function mb_strlen;
use function mb_strpos;
use function mb_substr;
use function mb_trim;
use function min;
use function str_repeat;
use function str_starts_with;

/**
 * Updates all ATX headings levels.
 */
readonly class Renumber extends Base implements Mutation {
    public function __construct(
        /**
         * @var int<1, 6>
         */
        protected int $startLevel,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        // Just in case
        yield from [];

        // Process
        $highest  = static::MaxLevel;
        $headings = $this->getHeadings($document, $highest);
        $diff     = $this->startLevel - $highest;

        if ($diff === 0) {
            return;
        }

        foreach ($headings as [$heading, $location, $text]) {
            $level  = min(static::MaxLevel, $heading->getLevel() + $diff);
            $prefix = mb_substr($text, 0, (int) mb_strpos($text, '#'));
            $eols   = mb_strlen($text) - mb_strlen(mb_trim($text, "\n"));
            $text   = mb_substr($text, mb_strlen($prefix));
            $text   = $prefix.str_repeat('#', $level).' '.mb_trim(mb_trim($text, '#')).str_repeat("\n", $eols);

            yield [$location, $text];
        }
    }

    /**
     * @return list<array{Heading, Location, string}>
     */
    private function getHeadings(Document $document, int &$highest): array {
        $headings = [];

        foreach ($this->nodes($document) as $heading) {
            // ATX?
            $location = LocationData::get($heading);
            $line     = $document->getText($location);

            if (!str_starts_with(mb_trim($line), '#')) {
                continue;
            }

            // Ok
            $headings[] = [$heading, $location, $line];
            $highest    = min($highest, $heading->getLevel());
        }

        return $headings;
    }
}
