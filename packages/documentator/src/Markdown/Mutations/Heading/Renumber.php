<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use Override;

use function mb_rtrim;
use function mb_strlen;
use function min;
use function str_repeat;
use function str_replace;

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
            $setext   = Utils::isHeadingSetext($heading);
            $level    = min(static::MaxLevel, $node->getLevel() + $diff);
            $eols     = str_repeat("\n", mb_strlen($heading) - mb_strlen(mb_rtrim($heading, "\n")));
            $text     = Utils::getHeadingText($heading);
            $prefix   = '';
            $suffix   = '';

            if ($setext && $level <= 2) {
                $suffix = "\n".str_repeat($level === 1 ? '=' : '-', 5);
            } else {
                $prefix = str_repeat('#', $level).' ';
                $text   = str_replace("\n", ' ', $text);
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
}
