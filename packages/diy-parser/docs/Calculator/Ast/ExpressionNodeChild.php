<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Docs\Calculator\Ast;

use LastDragon_ru\DiyParser\Ast\NodeChild;

/**
 * @extends NodeChild<ExpressionNode>
 */
interface ExpressionNodeChild extends Node, NodeChild {
    // empty
}
