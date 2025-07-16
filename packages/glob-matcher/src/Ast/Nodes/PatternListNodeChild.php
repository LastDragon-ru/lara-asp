<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\NodeChild;
use LastDragon_ru\GlobMatcher\Ast\Node;

/**
 * @extends NodeChild<PatternListNode>
 */
interface PatternListNodeChild extends Node, NodeChild {
    // empty
}
