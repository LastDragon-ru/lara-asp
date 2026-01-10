<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Nullable;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\NodeIterator;
use Override;

/**
 * @internal
 * @extends Nullable<Heading>
 */
readonly class TitleData extends Nullable {
    #[Override]
    protected static function default(Node $node): mixed {
        // Document?
        if (!($node instanceof Document)) {
            return null;
        }

        // Search
        $title = null;

        foreach ($node->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $child) {
            // Document?
            if ($child instanceof Document) {
                continue;
            }

            // Comment?
            if ($child instanceof HtmlBlock && $child->getType() === HtmlBlock::TYPE_2_COMMENT) {
                continue;
            }

            // Title?
            if ($child instanceof Heading && $child->getLevel() === 1) {
                $title = $child;
            }

            // Only first needed
            break;
        }

        // Return
        return $title;
    }
}
