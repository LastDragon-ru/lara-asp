<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Comment;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Node\Node;
use Override;

use function str_ends_with;
use function str_starts_with;

/**
 * Removes all comments (HTML & `[//]: #`).
 *
 * @implements Mutation<HtmlBlock|HtmlInline|ReferenceNode>
 */
readonly class Remove implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            HtmlBlock::class,
            HtmlInline::class,
            ReferenceNode::class,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        return $this->isComment($node)
            ? [new Delete(Location::get($node))]
            : [];
    }

    private function isComment(HtmlBlock|HtmlInline|ReferenceNode $node): bool {
        $comment = false;

        if ($node instanceof HtmlBlock) {
            $comment = $node->getType() === HtmlBlock::TYPE_2_COMMENT;
        } elseif ($node instanceof HtmlInline) {
            $comment = str_starts_with($node->getLiteral(), '<!--')
                && str_ends_with($node->getLiteral(), '-->');
        } else {
            $comment = $node->getLabel() === '//'
                && $node->getDestination() === '#';
        }

        return $comment;
    }
}
