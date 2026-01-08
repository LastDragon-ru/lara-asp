<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\TextParser\Ast\Cursor;
use LastDragon_ru\TextParser\Ast\NodeString;
use Override;

class StringNode extends NodeString implements BraceExpansionNodeChild {
    /**
     * @inheritDoc
     */
    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        return [$cursor->node->string];
    }
}
