<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Heading;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content as ContentData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\NodeIterator;
use Override;

use function min;
use function str_repeat;

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
            $content  = ContentData::get($head);
            $level    = min(static::MaxLevel, $head->getLevel() + $diff);

            if ($location->offset === $content->offset) {
                // Setext ...
                $underline = new Location(
                    $content->endLine + 1,
                    $location->endLine,
                    0,
                    null,
                    0,
                    $location->internalPadding,
                );

                if ($level <= 2) {
                    // ... to Setext -> just update underline
                    $mutagens[] = new Replace($underline, str_repeat($level === 1 ? '=' : '-', 5));
                } elseif ($content->startLine !== $content->endLine) {
                    // ... multiline to ATX -> remove underline, convert to single line and replace
                    $heading    = $document->getText($content);
                    $heading    = Text::toSingleLine($heading);
                    $mutagens[] = new Delete($underline);
                    $mutagens[] = new Replace($content, str_repeat('#', $level).' '.$heading);
                } else {
                    // ... to ATX -> remove underline, add #
                    $mutagens[] = new Delete($underline);
                    $mutagens[] = new Replace($content->before(), str_repeat('#', $level).' ');
                }
            } else {
                // AXT to ATX => update #
                $prefix     = str_repeat('#', $level);
                $mutagens[] = new Replace(
                    $location->before()->withLength($content->offset - $location->offset),
                    $prefix.' ',
                );

                if ($content->length !== null) {
                    // Has # on the end
                    $mutagens[] = new Replace(
                        $content->after()->withLength(null),
                        ' '.$prefix,
                    );
                }
            }
        }

        return $mutagens;
    }

    /**
     * @return list<Heading>
     */
    protected function headings(DocumentNode $document, int &$initial = 0): array {
        $headings = [];

        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if (!($node instanceof Heading) || Utils::isInside($node, BlockQuote::class)) {
                continue;
            }

            $initial    = min($initial, $node->getLevel());
            $headings[] = $node;
        }

        return $headings;
    }
}
