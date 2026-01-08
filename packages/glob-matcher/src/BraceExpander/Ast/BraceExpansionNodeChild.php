<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\TextParser\Ast\NodeChild;

/**
 * @extends NodeChild<BraceExpansionNode>
 */
interface BraceExpansionNodeChild extends Node, NodeChild {
    // empty
}
