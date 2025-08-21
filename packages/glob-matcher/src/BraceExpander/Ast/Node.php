<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\DiyParser\Ast\NodeChild;

/**
 * @extends NodeChild<self>
 */
interface Node extends NodeChild {
    /**
     * @param Cursor<covariant static> $cursor
     *
     * @return iterable<mixed, string>
     */
    public static function toIterable(Cursor $cursor): iterable;
}
