<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(BufferedStream::class)]
final class BufferedStreamTest extends TestCase {
    public function testIterator(): void {
        $items  = [0, 1, 2, 3, 4, 5];
        $stream = new BufferedStream($items, 2, 2);
        $actual = iterator_to_array($stream, false);

        self::assertSame($items, $actual);
    }

    public function testBuffer(): void {
        $stream = new BufferedStreamTest__Stream([0, 1, 2, 3, 4, 5], 2, 2);

        $stream->rewind();

        self::assertSame(
            [
                'key'    => 0,
                'value'  => 0,
                'cursor' => 0,
                'buffer' => [0, 1, 2],
            ],
            $stream->debug(),
        );

        $stream->next();

        self::assertSame(
            [
                'key'    => 1,
                'value'  => 1,
                'cursor' => 1,
                'buffer' => [0, 1, 2, 3],
            ],
            $stream->debug(),
        );

        $stream->next();

        self::assertSame(
            [
                'key'    => 2,
                'value'  => 2,
                'cursor' => 2,
                'buffer' => [0, 1, 2, 3, 4],
            ],
            $stream->debug(),
        );

        $stream->next();

        self::assertSame(
            [
                'key'    => 3,
                'value'  => 3,
                'cursor' => 2,
                'buffer' => [1, 2, 3, 4, 5],
            ],
            $stream->debug(),
        );

        $stream->next();

        self::assertSame(
            [
                'key'    => 4,
                'value'  => 4,
                'cursor' => 2,
                'buffer' => [2, 3, 4, 5],
            ],
            $stream->debug(),
        );

        $stream->next();

        self::assertSame(
            [
                'key'    => 5,
                'value'  => 5,
                'cursor' => 2,
                'buffer' => [3, 4, 5],
            ],
            $stream->debug(),
        );

        $stream->next();

        self::assertFalse($stream->valid());

        $stream->rewind();

        self::assertSame(
            [
                'key'    => 0,
                'value'  => 0,
                'cursor' => 0,
                'buffer' => [0, 1, 2],
            ],
            $stream->debug(),
        );
    }

    public function testSeek(): void {
        $stream = new BufferedStreamTest__Stream([0, 1, 2, 3, 4, 5], 2, 2);

        $stream->rewind();
        $stream->next();
        $stream->next();

        self::assertSame(
            [
                'key'    => 2,
                'value'  => 2,
                'cursor' => 2,
                'buffer' => [0, 1, 2, 3, 4],
            ],
            $stream->debug(),
        );

        $stream->seek($stream->key() - 1);

        self::assertSame(
            [
                'key'    => 1,
                'value'  => 1,
                'cursor' => 1,
                'buffer' => [0, 1, 2, 3, 4],
            ],
            $stream->debug(),
        );
    }

    public function testSeekOutOfBounds(): void {
        self::expectException(OutOfBoundsException::class);

        $items  = [0, 1, 2];
        $stream = new BufferedStream($items, 2, 2);

        $stream->seek(123);
    }

    public function testArrayAccessImplementation(): void {
        $stream = new BufferedStream([0, 1, 2, 3, 4], 2, 2);

        $stream->rewind();
        $stream->next();

        self::assertSame(1, $stream->current());
        self::assertTrue(isset($stream[2]));
        self::assertSame(3, $stream[2]);
        self::assertTrue(isset($stream[0]));
        self::assertSame(1, $stream[0]);
        self::assertFalse(isset($stream[10]));
        self::assertTrue(isset($stream[-1]));
        self::assertSame(0, $stream[-1]);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @template TValue
 * @extends BufferedStream<TValue>
 */
class BufferedStreamTest__Stream extends BufferedStream {
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
