<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\NodeIterator;
use Override;

use function mb_rtrim;
use function mb_strlen;
use function min;
use function str_repeat;
use function str_replace;

/**
 * Updates all headings levels.
 *
 * @implements Mutation<DocumentNode>
 */
readonly class Renumber implements Mutation {
    protected const int MaxLevel = 6;

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
    public static function nodes(): array {
        return [
            DocumentNode::class,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        // Update?
        $initial = static::MaxLevel;
        $nodes   = $this->headings($node, $initial);
        $diff    = $this->startLevel - $initial;

        if ($diff === 0) {
            return [];
        }

        // Process
        $mutagens = [];

        foreach ($nodes as $head) {
            $location = LocationData::get($head);
            $heading  = $document->getText($location);
            $setext   = Utils::isHeadingSetext($heading);
            $level    = min(static::MaxLevel, $head->getLevel() + $diff);
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

            $mutagens[] = new Replace($location, $prefix.$text.$suffix.$eols);
        }

        return $mutagens;
    }

    /**
     * @return list<Heading>
     */
    protected function headings(DocumentNode $document, int &$initial = 0): array {
        $headings = [];

        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if (!($node instanceof Heading)) {
                continue;
            }

            $initial    = min($initial, $node->getLevel());
            $headings[] = $node;
        }

        return $headings;
    }
}
