<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\DiyParser\Ast\NodeChild;

/**
 * @extends NodeChild<NameNode|PatternNode>
 */
interface NameNodeChild extends Node, NodeChild {
    // empty
}
