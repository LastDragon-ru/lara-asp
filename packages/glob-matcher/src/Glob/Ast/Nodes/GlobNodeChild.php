<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\NodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Node;

/**
 * @extends NodeChild<GlobNode>
 */
interface GlobNodeChild extends Node, NodeChild {
    // empty
}
