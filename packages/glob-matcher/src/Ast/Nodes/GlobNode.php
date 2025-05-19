<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\DiyParser\Iterables\TransformIterable;
use LastDragon_ru\DiyParser\Utils;
use LastDragon_ru\GlobMatcher\Ast\ParentNode;
use LastDragon_ru\GlobMatcher\Options;
use Override;

/**
 * @extends ParentNode<GlobNodeChild>
 */
class GlobNode extends ParentNode {
    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        $children = new TransformIterable($cursor, static function (Cursor $child) use ($options): string {
            $regex = $child->node::toRegex($options, $child);
            $regex = $regex !== '' ? "(?:{$regex})" : '';

            return $regex;
        });
        $regex    = Utils::toString($children);

        return $regex;
    }
}
