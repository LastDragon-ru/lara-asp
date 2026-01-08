<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\TextParser\Ast\Cursor;
use LastDragon_ru\TextParser\Ast\NodeString;
use Override;

use function preg_quote;

class StringNode extends NodeString implements Node, NameNodeChild, CharacterNodeChild {
    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        return preg_quote($cursor->node->string);
    }
}
