<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\NodeString;
use LastDragon_ru\GlobMatcher\Ast\Node;

class StringNode extends NodeString implements Node, NameNodeChild, CharacterNodeChild {
    // empty
}
