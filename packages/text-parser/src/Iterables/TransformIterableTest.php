<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TransformIterable::class)]
final class TransformIterableTest extends TestCase {
    public function testGetIterator(): void {
        self::assertSame(
            [2, 4, 6, 8, 10],
            iterator_to_array(new TransformIterable([1, 2, 3, 4, 5], static fn ($v) => 2 * $v), false),
        );
    }
}
