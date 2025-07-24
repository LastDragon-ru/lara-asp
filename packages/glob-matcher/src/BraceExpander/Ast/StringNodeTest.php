<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(StringNode::class)]
final class StringNodeTest extends TestCase {
    public function testToIterable(): void {
        $node   = new StringNode('string');
        $cursor = new Cursor($node);

        self::assertSame(
            [$node->string],
            iterator_to_array($node::toIterable($cursor), false),
        );
    }
}
