<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Docs\Calculator\Ast;

use LastDragon_ru\DiyParser\Ast\NodeParentImpl;

/**
 * @template TChild of Node
 *
 * @extends NodeParentImpl<TChild>
 */
abstract class ParentNode extends NodeParentImpl implements Node {
    // empty
}
