<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\DiyParser\Ast\NodeString;
use LastDragon_ru\GlobMatcher\Ast\Node;
use LastDragon_ru\GlobMatcher\Options;
use Override;

use function preg_quote;

class StringNode extends NodeString implements Node, NameNodeChild, CharacterNodeChild {
    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        return preg_quote($cursor->node->string);
    }
}
