<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Glob\Ast\ParentNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Utils;
use LastDragon_ru\GlobMatcher\Glob\Options;
use Override;

/**
 * @extends ParentNode<GlobNodeChild>
 */
class GlobNode extends ParentNode {
    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        return Utils::toRegex($options, $cursor);
    }
}
