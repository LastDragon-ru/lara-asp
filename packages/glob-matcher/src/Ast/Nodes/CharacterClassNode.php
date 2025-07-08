<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Ast\Node;

class CharacterClassNode implements Node, CharacterNodeChild {
    public function __construct(
        public CharacterClass $class,
    ) {
        // empty
    }
}
