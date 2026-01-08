<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\TextParser\Ast\Cursor;
use Override;

use function count;
use function str_starts_with;

/**
 * @extends ParentNode<NameNodeChild>
 */
class NameNode extends ParentNode implements GlobNodeChild {
    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        // `.` and `..` must always be matched explicitly
        if (count($cursor) === 1 && $cursor[0] !== null && self::isDot($cursor[0]->node)) {
            return $cursor[0]->node::toRegex($options, $cursor[0]);
        }

        // Regex
        $regex = '(?=.)'.Utils::toRegex($options, $cursor);

        // By default, the `.` at the start of a path or immediately
        // following a slash must be matched explicitly.
        if ($options->hidden || ($cursor[0] !== null && self::isExplicitDot($cursor[0]->node))) {
            $regex = "(?!\\.{1,2}(?:/|$))(?:{$regex})";
        } else {
            $regex = "(?!\\.)(?:{$regex})";
        }

        // Return
        return $regex;
    }

    private static function isDot(?Node $node): bool {
        return $node instanceof StringNode
            && ($node->string === '.' || $node->string === '..');
    }

    private static function isExplicitDot(?Node $node): bool {
        return $node instanceof StringNode
            && str_starts_with($node->string, '.');
    }
}
