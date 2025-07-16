<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Ast\ParentNode;
use LastDragon_ru\GlobMatcher\Ast\Utils;
use LastDragon_ru\GlobMatcher\Options;
use Override;

use function count;

/**
 * @extends ParentNode<PatternListNodeChild>
 */
class PatternListNode extends ParentNode implements NameNodeChild {
    /**
     * @param list<PatternListNodeChild> $children
     */
    public function __construct(
        public PatternListQuantifier $quantifier,
        array $children,
    ) {
        parent::__construct($children);
    }

    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        // Empty?
        if (count($cursor) === 0) {
            return '';
        }

        // Not not?
        // - Optimize

        // Build
        $regex = Utils::toRegex($options, $cursor, '|');
        $regex = match ($cursor->node->quantifier) {
            PatternListQuantifier::ZeroOrOne  => "(?:{$regex})?",
            PatternListQuantifier::ZeroOrMore => "(?:{$regex})*",
            PatternListQuantifier::OneOrMore  => "(?:{$regex})+",
            PatternListQuantifier::OneOf      => "(?:{$regex})",
            PatternListQuantifier::Not        => static::toRegexBuildNot($options, $cursor, $regex),
        };

        return $regex;
    }

    /**
     * @param Cursor<covariant static> $cursor
     */
    protected static function toRegexBuildNot(Options $options, Cursor $cursor, string $regex): string {
        // Regex negative lookahead differs from glob `!(...)`, for this reason
        // we should include the following (till the `/` or end of pattern)
        // nodes to the not regex.
        $following = '';
        $parent    = $cursor;
        $last      = true;

        while ($parent !== null) {
            if (!($parent->node instanceof PatternNode)) {
                $next = $parent->next;

                while ($next !== null) {
                    if ($next->node instanceof SegmentNode) {
                        break 2;
                    }

                    $following .= '(?:'.$next->node::toRegex($options, $next).')';
                    $next       = $next->next;
                    $last       = false;
                }
            }

            $parent = $parent->parent;
        }

        // Build
        $following = $following !== '' ? "(?:{$following})" : '';
        $anything  = '[^/]*?';
        $regex     = $last
            ? "(?!(?:{$regex}){$following}(?:$|\/)){$anything}"
            : "(?!(?:{$regex}){$following}){$anything}";

        return "(?:{$regex})";
    }
}
