<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use LastDragon_ru\TextParser\Exceptions\OffsetOutOfBounds;
use LastDragon_ru\TextParser\Exceptions\OffsetReadonly;
use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TransactionalIterable::class)]
final class TransactionalIterableTest extends TestCase {
    public function testNext(): void {
        $source   = ['a', 'b', 'c', 'd', 'e'];
        $iterable = new TransactionalIterable($source, 10, 5);

        self::assertSame('a', $iterable[0]);

        $iterable->next(1);

        self::assertSame('b', $iterable[0]);

        $iterable->next(2);

        self::assertSame('d', $iterable[0]);

        $iterable->next(5);
    }

    public function testTransactions(): void {
        $source   = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'];
        $iterable = new TransactionalIterable($source, 15, 5);
        $actual   = '';
        $expected = 'abc[cde!cd[def[fgh!f]ghi';

        foreach ($iterable as $value) {
            $actual .= $value;

            if ($value === 'c') {
                $actual .= '[';

                $iterable->begin();

                foreach ($iterable as $c) {
                    $actual .= $c;

                    if ($c === 'e') {
                        break;
                    }
                }

                $iterable->rollback();

                $actual .= '!';
                $actual .= $iterable[0];
            }

            if ($value === 'd') {
                $actual .= '[';

                $iterable->begin();

                foreach ($iterable as $d) {
                    $actual .= $d;

                    if ($d === 'f') {
                        $actual .= '[';

                        $iterable->begin();

                        foreach ($iterable as $f) {
                            $actual .= $f;

                            if ($f === 'h') {
                                break;
                            }
                        }

                        $iterable->end(null);

                        $actual .= '!';
                        $actual .= $iterable[0];

                        break;
                    }
                }

                $iterable->commit();

                $actual .= ']';
            }
        }

        self::assertSame($expected, $actual);
    }

    public function testOffsetExists(): void {
        $source   = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'];
        $iterable = new TransactionalIterable($source, 5, 5);

        self::assertFalse(isset($iterable[123]));
        self::assertTrue(isset($iterable[3]));
    }

    public function testOffsetGet(): void {
        $source   = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'];
        $iterable = new TransactionalIterable($source, 5, 5);

        $iterable->next(4);

        self::assertSame('e', $iterable[0]);
        self::assertSame('e', $iterable[0]);

        self::assertSame('f', $iterable[1]);
        self::assertSame('h', $iterable[3]);

        self::assertSame('d', $iterable[-1]);
        self::assertSame('b', $iterable[-3]);
    }

    public function testOffsetGetNegativeOutOfBoundsException(): void {
        self::expectExceptionObject(new OffsetOutOfBounds(-6));

        $source   = ['a', 'b'];
        $iterable = new TransactionalIterable($source, 5, 5);

        self::assertNull($iterable[-6]);
    }

    public function testOffsetGetPositiveOutOfBoundsException(): void {
        self::expectExceptionObject(new OffsetOutOfBounds(6));

        $source   = ['a', 'b'];
        $iterable = new TransactionalIterable($source, 5, 5);

        self::assertNull($iterable[6]);
    }

    public function testOffsetSet(): void {
        self::expectExceptionObject(new OffsetReadonly(0));

        $source   = ['a', 'b'];
        $iterable = new TransactionalIterable($source, 5, 5);

        $iterable[0] = 'c';
    }

    public function testOffsetUnset(): void {
        self::expectExceptionObject(new OffsetReadonly(0));

        $source   = ['a', 'b'];
        $iterable = new TransactionalIterable($source, 5, 5);

        unset($iterable[0]);
    }

    public function testIterator(): void {
        // Prepare
        $source   = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $iterable = new TransactionalIterable($source, 3, 10);

        // Top level
        $top = [];

        foreach ($iterable as $value) {
            $top[] = $value;

            if ($value === 'b') {
                break;
            }
        }

        self::assertSame(['a', 'b'], $top);
        self::assertSame('b', $iterable->current());
        self::assertTrue($iterable->valid());

        // No Rewind
        $rewind = [];

        foreach ($iterable as $value) {
            $rewind[] = $value;

            if ($value === 'd') {
                break;
            }
        }

        self::assertSame(['b', 'c', 'd'], $rewind);
        self::assertSame('d', $iterable->current());
        self::assertTrue($iterable->valid());

        // Transaction is limited
        $transaction = [];

        $iterable->begin();

        foreach ($iterable as $value) {
            $transaction[] = $value;
        }

        $iterable->rollback();

        self::assertSame(['d', 'e', 'f'], $transaction);
        self::assertSame('d', $iterable->current());
        self::assertTrue($iterable->valid());

        // Rest
        $rest = [];

        foreach ($iterable as $value) {
            $rest[] = $value;
        }

        self::assertSame(['d', 'e', 'f', 'g'], $rest);
        self::assertFalse($iterable->valid());
    }

    public function testInside(): void {
        $iterable = new TransactionalIterable([1, 2, 3], 1, 1);

        self::assertFalse($iterable->isInside(null));
        self::assertFalse($iterable->isInside(__METHOD__));

        $iterable->begin(null);

        self::assertFalse($iterable->isInside(null));
        self::assertFalse($iterable->isInside(__METHOD__));

        $iterable->begin(__METHOD__);

        self::assertTrue($iterable->isInside(null));
        self::assertFalse($iterable->isInside(__METHOD__));

        $iterable->begin(__METHOD__);

        self::assertTrue($iterable->isInside(null));
        self::assertTrue($iterable->isInside(__METHOD__));

        $iterable->commit();

        self::assertTrue($iterable->isInside(null));
        self::assertFalse($iterable->isInside(__METHOD__));

        $iterable->rollback();

        self::assertFalse($iterable->isInside(null));
        self::assertFalse($iterable->isInside(__METHOD__));
    }

    public function testProperties(): void {
        $iterable = new TransactionalIterable([1, 2, 3], 1, 1);

        self::assertSame(0, $iterable->level);
        self::assertNull($iterable->name);

        $iterable->begin(null);

        self::assertSame(1, $iterable->level);
        self::assertNull($iterable->name);

        $iterable->begin(__METHOD__);

        self::assertSame(2, $iterable->level);
        self::assertSame(__METHOD__, $iterable->name);

        $iterable->commit();

        self::assertSame(1, $iterable->level);
        self::assertNull($iterable->name);

        $iterable->rollback();

        self::assertSame(0, $iterable->level);
        self::assertNull($iterable->name);
    }
}
