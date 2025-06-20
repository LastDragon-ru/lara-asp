<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use IteratorIterator;
use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TokenStream::class)]
final class TokenStreamTest extends TestCase {
    public function testGetIterator(): void {
        $source = ['aaaaABBBBb[', ']aaaa ! B * BBB'];

        self::assertEquals(
            [
                new Token(TokenStreamTest_EnumAlpha::String, 'aaaa', 0),
                new Token(TokenStreamTest_EnumAlpha::A, 'A', 4),
                new Token(TokenStreamTest_EnumAlpha::String, 'BBBB', 5),
                new Token(TokenStreamTest_EnumAlpha::B, 'b', 9),
                new Token(TokenStreamTest_EnumAlpha::String, '[]aaaa ! B * BBB', 10),
            ],
            iterator_to_array(
                new TokenStream($source, TokenStreamTest_EnumAlpha::class, TokenStreamTest_EnumAlpha::String),
                false,
            ),
        );
        self::assertEquals(
            [
                new Token(TokenStreamTest_EnumAlpha::String, 'aaaa', 0),
                new Token(TokenStreamTest_EnumAlpha::A, 'A', 4),
                new Token(TokenStreamTest_EnumAlpha::String, 'BBBB', 5),
                new Token(TokenStreamTest_EnumAlpha::B, 'b', 9),
                new Token(TokenStreamTest_EnumSpecial::Brackets, '[]', 10),
                new Token(TokenStreamTest_EnumAlpha::String, 'aaaa ! B ', 12),
                new Token(TokenStreamTest_EnumSpecial::Asterisk, '*', 21),
                new Token(TokenStreamTest_EnumAlpha::String, ' BBB', 22),
            ],
            iterator_to_array(
                new TokenStream(
                    $source,
                    [TokenStreamTest_EnumAlpha::class, TokenStreamTest_EnumSpecial::class],
                    TokenStreamTest_EnumAlpha::String,
                ),
                false,
            ),
        );
    }

    public function testRewind(): void {
        $source   = ['aaaaABBBBb[', ']aaaa ! B * BBB'];
        $stream   = new TokenStream($source, TokenStreamTest_EnumAlpha::class, TokenStreamTest_EnumAlpha::String);
        $iterator = new IteratorIterator($stream->getIterator());

        $iterator->rewind();

        self::assertTrue($iterator->valid());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenStreamTest_EnumAlpha: string {
    case String = '';
    case A      = 'A';
    case B      = 'b';
}

enum TokenStreamTest_EnumSpecial: string {
    case Question = '?';
    case Asterisk = '*';
    case Brackets = '[]';
}
