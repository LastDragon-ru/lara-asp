<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\DiyParser\Ast\NodeChild;

/**
 * @extends NodeChild<GlobNode>
 */
interface GlobNodeChild extends Node, NodeChild {
    // empty
}
