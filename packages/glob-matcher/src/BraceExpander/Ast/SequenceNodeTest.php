<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\GlobMatcher\Package\TestCase;
use LastDragon_ru\TextParser\Ast\Cursor;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(SequenceNode::class)]
final class SequenceNodeTest extends TestCase {
    public function testToIterable(): void {
        $node = new SequenceNode([
            new class () implements SequenceNodeChild {
                /**
                 * @inheritDoc
                 */
                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return ['aa', 'ab'];
                }
            },
            new class () implements SequenceNodeChild {
                /**
                 * @inheritDoc
                 */
                #[Override]
                public static function toIterable(Cursor $cursor): iterable {
                    return ['ba', 'bb', 'bc'];
                }
            },
        ]);

        self::assertSame(
            ['aa', 'ab', 'ba', 'bb', 'bc'],
            iterator_to_array($node::toIterable(new Cursor($node)), false),
        );
    }
}
