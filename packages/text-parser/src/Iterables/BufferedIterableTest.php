<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use LastDragon_ru\TextParser\Exceptions\OffsetOutOfBounds;
use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(BufferedIterable::class)]
final class BufferedIterableTest extends TestCase {
    public function testIterator(): void {
        $items    = [0, 1, 2, 3, 4, 5];
        $iterable = new BufferedIterable($items, 2, 2);
        $actual   = iterator_to_array($iterable, false);

        self::assertSame($items, $actual);
    }

    public function testBuffer(): void {
        $iterable = new BufferedIterableTest__Iterable([0, 1, 2, 3, 4, 5], 2, 2);

        $iterable->rewind();

        self::assertSame(
            [
                'key'    => 0,
                'value'  => 0,
                'cursor' => 0,
                'buffer' => [0, 1, 2],
            ],
            $iterable->debug(),
        );

        $iterable->next();

        self::assertSame(
            [
                'key'    => 1,
                'value'  => 1,
                'cursor' => 1,
                'buffer' => [0, 1, 2, 3],
            ],
            $iterable->debug(),
        );

        $iterable->next();

        self::assertSame(
            [
                'key'    => 2,
                'value'  => 2,
                'cursor' => 2,
                'buffer' => [0, 1, 2, 3, 4],
            ],
            $iterable->debug(),
        );

        $iterable->next();

        self::assertSame(
            [
                'key'    => 3,
                'value'  => 3,
                'cursor' => 2,
                'buffer' => [1, 2, 3, 4, 5],
            ],
            $iterable->debug(),
        );

        $iterable->next();

        self::assertSame(
            [
                'key'    => 4,
                'value'  => 4,
                'cursor' => 2,
                'buffer' => [2, 3, 4, 5],
            ],
            $iterable->debug(),
        );

        $iterable->next();

        self::assertSame(
            [
                'key'    => 5,
                'value'  => 5,
                'cursor' => 2,
                'buffer' => [3, 4, 5],
            ],
            $iterable->debug(),
        );

        $iterable->next();

        self::assertFalse($iterable->valid());

        $iterable->rewind();

        self::assertSame(
            [
                'key'    => 0,
                'value'  => 0,
                'cursor' => 0,
                'buffer' => [0, 1, 2],
            ],
            $iterable->debug(),
        );
    }

    public function testSeek(): void {
        $iterable = new BufferedIterableTest__Iterable([0, 1, 2, 3, 4, 5], 2, 2);

        $iterable->rewind();
        $iterable->next();
        $iterable->next();

        self::assertSame(
            [
                'key'    => 2,
                'value'  => 2,
                'cursor' => 2,
                'buffer' => [0, 1, 2, 3, 4],
            ],
            $iterable->debug(),
        );

        $iterable->seek($iterable->key() - 1);

        self::assertSame(
            [
                'key'    => 1,
                'value'  => 1,
                'cursor' => 1,
                'buffer' => [0, 1, 2, 3, 4],
            ],
            $iterable->debug(),
        );
    }

    public function testSeekOutOfBounds(): void {
        self::expectExceptionObject(new OffsetOutOfBounds(123));

        $items    = [0, 1, 2];
        $iterable = new BufferedIterable($items, 2, 2);

        $iterable->seek(123);
    }

    public function testArrayAccessImplementation(): void {
        $iterable = new BufferedIterable([0, 1, 2, 3, 4], 2, 2);

        $iterable->rewind();
        $iterable->next();

        self::assertSame(1, $iterable->current());
        self::assertTrue(isset($iterable[2]));
        self::assertSame(3, $iterable[2]);
        self::assertTrue(isset($iterable[0]));
        self::assertSame(1, $iterable[0]);
        self::assertFalse(isset($iterable[10]));
        self::assertTrue(isset($iterable[-1]));
        self::assertSame(0, $iterable[-1]);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @template TValue
 * @extends BufferedIterable<TValue>
 */
class BufferedIterableTest__Iterable extends BufferedIterable {
    /**
     * @return array{key: int, value: TValue, cursor: int, buffer: list<TValue>}
     */
    public function debug(): array {
        $buffer = [];

        foreach ($this->buffer as $v) {
            $buffer[] = $v;
        }

        return [
            'key'    => $this->key,
            'value'  => $this->current(),
            'cursor' => $this->cursor,
            'buffer' => $buffer,
        ];
    }
}
