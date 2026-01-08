<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\TextParser\Ast\Cursor;
use LastDragon_ru\TextParser\Iterables\TransformIterable;
use LastDragon_ru\TextParser\Utils as ParserUtils;

use function count;

class Utils {
    /**
     * @param Cursor<covariant ParentNode<covariant Node>> $cursor
     */
    public static function toRegex(Options $options, Cursor $cursor, string $separator = ''): string {
        $regex = '';

        if (count($cursor) > 1) {
            $children = new TransformIterable($cursor, static function (Cursor $child) use ($options): string {
                $regex = $child->node::toRegex($options, $child);
                $regex = $regex !== '' ? "(?:{$regex})" : '';

                return $regex;
            });
            $regex    = ParserUtils::toString($children, $separator);
        } elseif (isset($cursor[0])) {
            $regex = $cursor[0]->node::toRegex($options, $cursor[0]);
        } else {
            // empty
        }

        return $regex;
    }
}
