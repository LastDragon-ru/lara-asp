<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;
use LastDragon_ru\GlobMatcher\Glob\Options;
use Override;

class CharacterClassNode implements Node, CharacterNodeChild {
    public function __construct(
        public CharacterClass $class,
    ) {
        // empty
    }

    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        return "[:{$cursor->node->class->value}:]";
    }
}
