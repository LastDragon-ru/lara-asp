<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use IteratorIterator;
use LastDragon_ru\TextParser\Package\TestCase;
use LastDragon_ru\TextParser\Tokenizer\Token;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(StringTokenizeIterable::class)]
final class StringTokenizeIterableTest extends TestCase {
    public function testGetIterator(): void {
        $source = ['aaaaABBBBb[', ']aaaa ! B * BBB'];

        self::assertEquals(
            [
                new Token(null, 'aaaa', 0),
                new Token(StringTokenizeIterableTest_EnumAlpha::A, 'A', 4),
                new Token(null, 'BBBB', 5),
                new Token(StringTokenizeIterableTest_EnumAlpha::B, 'b', 9),
                new Token(null, '[]aaaa ! B * BBB', 10),
            ],
            iterator_to_array(
                new StringTokenizeIterable(
                    $source,
                    StringTokenizeIterableTest_EnumAlpha::class,
                ),
                false,
            ),
        );
        self::assertEquals(
            [
                new Token(null, 'aaaa', 0),
                new Token(StringTokenizeIterableTest_EnumAlpha::A, 'A', 4),
                new Token(null, 'BBBB', 5),
                new Token(StringTokenizeIterableTest_EnumAlpha::B, 'b', 9),
                new Token(StringTokenizeIterableTest_EnumSpecial::Brackets, '[]', 10),
                new Token(null, 'aaaa ! B ', 12),
                new Token(StringTokenizeIterableTest_EnumSpecial::Asterisk, '*', 21),
                new Token(null, ' BBB', 22),
            ],
            iterator_to_array(
                new StringTokenizeIterable(
                    $source,
                    [StringTokenizeIterableTest_EnumAlpha::class, StringTokenizeIterableTest_EnumSpecial::class],
                ),
                false,
            ),
        );
    }

    public function testRewind(): void {
        $source   = ['aaaaABBBBb[', ']aaaa ! B * BBB'];
        $iterable = new StringTokenizeIterable(
            $source,
            StringTokenizeIterableTest_EnumAlpha::class,
        );
        $iterator = new IteratorIterator($iterable->getIterator());

        $iterator->rewind();

        self::assertTrue($iterator->valid());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum StringTokenizeIterableTest_EnumAlpha: string {
    case A = 'A';
    case B = 'b';
}

enum StringTokenizeIterableTest_EnumSpecial: string {
    case Question = '?';
    case Asterisk = '*';
    case Brackets = '[]';
}
