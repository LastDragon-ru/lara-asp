<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\TextParser\Ast\NodeParentImpl;

/**
 * @template TChild of Node
 *
 * @extends NodeParentImpl<TChild>
 */
abstract class ParentNode extends NodeParentImpl implements Node {
    // empty
}
