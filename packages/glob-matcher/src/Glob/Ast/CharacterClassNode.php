<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\TextParser\Ast\Cursor;
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
