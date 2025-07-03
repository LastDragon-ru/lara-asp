<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use LastDragon_ru\DiyParser\Exceptions\OffsetOutOfBounds;
use LastDragon_ru\DiyParser\Exceptions\OffsetReadonly;
use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TransactionalStream::class)]
final class TransactionalStreamTest extends TestCase {
    public function testNext(): void {
        $source = ['a', 'b', 'c', 'd', 'e'];
        $stream = new TransactionalStream($source, 10, 5);

        self::assertSame('a', $stream[0]);

        $stream->next(1);

        self::assertSame('b', $stream[0]);

        $stream->next(2);

        self::assertSame('d', $stream[0]);

        $stream->next(5);
    }

    public function testTransactions(): void {
        $source   = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'];
        $stream   = new TransactionalStream($source, 15, 5);
        $actual   = '';
        $expected = 'abc[cde!cd[def[fgh!f]ghi';

        foreach ($stream as $value) {
            $actual .= $value;

            if ($value === 'c') {
                $actual .= '[';

                $stream->begin();

                foreach ($stream as $c) {
                    $actual .= $c;

                    if ($c === 'e') {
                        break;
                    }
                }

                $stream->rollback();

                $actual .= '!';
                $actual .= $stream[0];
            }

            if ($value === 'd') {
                $actual .= '[';

                $stream->begin();

                foreach ($stream as $d) {
                    $actual .= $d;

                    if ($d === 'f') {
                        $actual .= '[';

                        $stream->begin();

                        foreach ($stream as $f) {
                            $actual .= $f;

                            if ($f === 'h') {
                                break;
                            }
                        }

                        $stream->end(null);

                        $actual .= '!';
                        $actual .= $stream[0];

                        break;
                    }
                }

                $stream->commit();

                $actual .= ']';
            }
        }

        self::assertSame($expected, $actual);
    }

    public function testOffsetExists(): void {
        $source = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'];
        $stream = new TransactionalStream($source, 5, 5);

        self::assertFalse(isset($stream[123]));
        self::assertTrue(isset($stream[3]));
    }

    public function testOffsetGet(): void {
        $source = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'];
        $stream = new TransactionalStream($source, 5, 5);

        $stream->next(4);

        self::assertSame('e', $stream[0]);
        self::assertSame('e', $stream[0]);

        self::assertSame('f', $stream[1]);
        self::assertSame('h', $stream[3]);

        self::assertSame('d', $stream[-1]);
        self::assertSame('b', $stream[-3]);
    }

    public function testOffsetGetNegativeOutOfBoundsException(): void {
        self::expectExceptionObject(new OffsetOutOfBounds(-6));

        $source = ['a', 'b'];
        $stream = new TransactionalStream($source, 5, 5);

        self::assertNull($stream[-6]);
    }

    public function testOffsetGetPositiveOutOfBoundsException(): void {
        self::expectExceptionObject(new OffsetOutOfBounds(6));

        $source = ['a', 'b'];
        $stream = new TransactionalStream($source, 5, 5);

        self::assertNull($stream[6]);
    }

    public function testOffsetSet(): void {
        self::expectExceptionObject(new OffsetReadonly(0));

        $source = ['a', 'b'];
        $stream = new TransactionalStream($source, 5, 5);

        $stream[0] = 'c';
    }

    public function testOffsetUnset(): void {
        self::expectExceptionObject(new OffsetReadonly(0));

        $source = ['a', 'b'];
        $stream = new TransactionalStream($source, 5, 5);

        unset($stream[0]);
    }

    public function testIterator(): void {
        // Prepare
        $source = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $stream = new TransactionalStream($source, 3, 10);

        // Top level
        $top = [];

        foreach ($stream as $value) {
            $top[] = $value;

            if ($value === 'b') {
                break;
            }
        }

        self::assertSame(['a', 'b'], $top);
        self::assertSame('b', $stream->current());
        self::assertTrue($stream->valid());

        // No Rewind
        $rewind = [];

        foreach ($stream as $value) {
            $rewind[] = $value;

            if ($value === 'd') {
                break;
            }
        }

        self::assertSame(['b', 'c', 'd'], $rewind);
        self::assertSame('d', $stream->current());
        self::assertTrue($stream->valid());

        // Transaction is limited
        $transaction = [];

        $stream->begin();

        foreach ($stream as $value) {
            $transaction[] = $value;
        }

        $stream->rollback();

        self::assertSame(['d', 'e', 'f'], $transaction);
        self::assertSame('d', $stream->current());
        self::assertTrue($stream->valid());

        // Rest
        $rest = [];

        foreach ($stream as $value) {
            $rest[] = $value;
        }

        self::assertSame(['d', 'e', 'f', 'g'], $rest);
        self::assertFalse($stream->valid());
    }
}
