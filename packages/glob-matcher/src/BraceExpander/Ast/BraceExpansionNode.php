<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\TextParser\Ast\Cursor;
use Override;

/**
 * @extends ParentNode<BraceExpansionNodeChild>
 */
class BraceExpansionNode extends ParentNode implements SequenceNodeChild {
    /**
     * @inheritDoc
     */
    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        yield from self::iterate($cursor, 0, '');
    }

    /**
     * @param Cursor<covariant static> $cursor
     *
     * @return iterable<mixed, string>
     */
    private static function iterate(Cursor $cursor, int $index, string $prefix): iterable {
        if (isset($cursor[$index])) {
            $iterable = $cursor[$index]->node::toIterable($cursor[$index]);

            foreach ($iterable as $string) {
                yield from self::iterate($cursor, $index + 1, $prefix.$string);
            }
        } else {
            yield $prefix;
        }
    }
}
