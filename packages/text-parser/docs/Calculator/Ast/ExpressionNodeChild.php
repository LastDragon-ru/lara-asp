<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use LastDragon_ru\TextParser\Ast\NodeChild;

/**
 * @extends NodeChild<ExpressionNode>
 */
interface ExpressionNodeChild extends Node, NodeChild {
    // empty
}
