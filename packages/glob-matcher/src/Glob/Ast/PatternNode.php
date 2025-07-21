<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Options;
use Override;

/**
 * @extends ParentNode<NameNodeChild>
 */
class PatternNode extends ParentNode implements PatternListNodeChild {
    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        return Utils::toRegex($options, $cursor);
    }
}
