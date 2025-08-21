<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\DiyParser\Ast\NodeChild;
use LastDragon_ru\GlobMatcher\Glob\Options;

/**
 * @extends NodeChild<self>
 */
interface Node extends NodeChild {
    /**
     * @param Cursor<covariant static> $cursor
     */
    public static function toRegex(Options $options, Cursor $cursor): string;
}
