<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Iterables;

use IteratorIterator;
use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use LastDragon_ru\DiyParser\Tokenizer\Token;
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
                new Token(StringTokenizeIterableTest_EnumAlpha::String, 'aaaa', 0),
                new Token(StringTokenizeIterableTest_EnumAlpha::A, 'A', 4),
                new Token(StringTokenizeIterableTest_EnumAlpha::String, 'BBBB', 5),
                new Token(StringTokenizeIterableTest_EnumAlpha::B, 'b', 9),
                new Token(StringTokenizeIterableTest_EnumAlpha::String, '[]aaaa ! B * BBB', 10),
            ],
            iterator_to_array(
                new StringTokenizeIterable(
                    $source,
                    StringTokenizeIterableTest_EnumAlpha::class,
                    StringTokenizeIterableTest_EnumAlpha::String,
                ),
                false,
            ),
        );
        self::assertEquals(
            [
                new Token(StringTokenizeIterableTest_EnumAlpha::String, 'aaaa', 0),
                new Token(StringTokenizeIterableTest_EnumAlpha::A, 'A', 4),
                new Token(StringTokenizeIterableTest_EnumAlpha::String, 'BBBB', 5),
                new Token(StringTokenizeIterableTest_EnumAlpha::B, 'b', 9),
                new Token(StringTokenizeIterableTest_EnumSpecial::Brackets, '[]', 10),
                new Token(StringTokenizeIterableTest_EnumAlpha::String, 'aaaa ! B ', 12),
                new Token(StringTokenizeIterableTest_EnumSpecial::Asterisk, '*', 21),
                new Token(StringTokenizeIterableTest_EnumAlpha::String, ' BBB', 22),
            ],
            iterator_to_array(
                new StringTokenizeIterable(
                    $source,
                    [StringTokenizeIterableTest_EnumAlpha::class, StringTokenizeIterableTest_EnumSpecial::class],
                    StringTokenizeIterableTest_EnumAlpha::String,
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
            StringTokenizeIterableTest_EnumAlpha::String,
        );
        $iterator = new IteratorIterator($iterable->getIterator());

        $iterator->rewind();

        self::assertTrue($iterator->valid());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum StringTokenizeIterableTest_EnumAlpha: string {
    case String = '';
    case A      = 'A';
    case B      = 'b';
}

enum StringTokenizeIterableTest_EnumSpecial: string {
    case Question = '?';
    case Asterisk = '*';
    case Brackets = '[]';
}
