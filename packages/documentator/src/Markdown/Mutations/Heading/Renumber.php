<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;

use function array_map;
use function array_slice;
use function implode;
use function mb_ltrim;
use function mb_rtrim;
use function mb_strlen;
use function mb_trim;
use function min;
use function str_repeat;
use function str_starts_with;

/**
 * Updates all headings levels.
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
        $initial = static::MaxLevel;
        $nodes   = $this->nodes($document, $initial);
        $diff    = $this->startLevel - $initial;

        if ($diff === 0) {
            return;
        }

        foreach ($nodes as $node) {
            $location = LocationData::get($node);
            $heading  = $document->getText($location);
            $setext   = $this->isSetext($heading);
            $level    = min(static::MaxLevel, $node->getLevel() + $diff);
            $eols     = str_repeat("\n", mb_strlen($heading) - mb_strlen(mb_rtrim($heading, "\n")));
            $text     = mb_trim($heading);
            $lines    = $setext
                ? array_slice(Text::getLines($text), 0, -1)
                : [mb_trim($text, '#')];
            $lines    = array_map(mb_trim(...), $lines);
            $prefix   = '';
            $suffix   = '';

            if ($setext && $level <= 2) {
                $text   = implode("\n", $lines);
                $suffix = "\n".str_repeat($level === 1 ? '=' : '-', 5);
            } else {
                $prefix = str_repeat('#', $level).' ';
                $text   = implode(' ', $lines);
            }

            yield [$location, $prefix.$text.$suffix.$eols];
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function nodes(Document $document, int &$initial = 0): iterable {
        $nodes = [];

        foreach (parent::nodes($document) as $node) {
            $initial = min($initial, $node->getLevel());
            $nodes[] = $node;
        }

        return $nodes;
    }

    private function isAtx(string $heading): bool {
        return str_starts_with(mb_ltrim($heading), '#');
    }

    private function isSetext(string $heading): bool {
        return !$this->isAtx($heading);
    }
}
