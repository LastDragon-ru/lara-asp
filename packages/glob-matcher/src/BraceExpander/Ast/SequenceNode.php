<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\TextParser\Ast\Cursor;
use Override;

/**
 * @extends ParentNode<SequenceNodeChild>
 */
class SequenceNode extends ParentNode implements BraceExpansionNodeChild, SequenceNodeChild {
    /**
     * @inheritDoc
     */
    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        foreach ($cursor as $child) {
            yield from $child->node::toIterable($child);
        }
    }
}
