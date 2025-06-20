<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TokenEscapeStream::class)]
final class TokenEscapeStreamTest extends TestCase {
    public function testGetIterator(): void {
        $tokens = [
            new Token(TokenEscapeStreamTest_Token::String, 'a', 0),
            new Token(TokenEscapeStreamTest_Token::Asterisk, '*', 1),
            new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 2),
            new Token(TokenEscapeStreamTest_Token::Asterisk, '*', 3),
            new Token(TokenEscapeStreamTest_Token::String, 'b', 4),
            new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 5),
            new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 6),
            new Token(TokenEscapeStreamTest_Token::Slash, '/', 7),
            new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 8),
            new Token(TokenEscapeStreamTest_Token::String, 'c', 9),
            new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 10),
        ];

        self::assertEquals(
            [
                new Token(TokenEscapeStreamTest_Token::String, 'a', 0),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 1),
                new Token(TokenEscapeStreamTest_Token::Asterisk, '*', 1),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 2),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 2),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 3),
                new Token(TokenEscapeStreamTest_Token::Asterisk, '*', 3),
                new Token(TokenEscapeStreamTest_Token::String, 'b', 4),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 5),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 5),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 6),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 6),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 7),
                new Token(TokenEscapeStreamTest_Token::Slash, '/', 7),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 8),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 8),
                new Token(TokenEscapeStreamTest_Token::String, 'c', 9),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 10),
                new Token(TokenEscapeStreamTest_Token::Backslash, '\\', 10),
            ],
            iterator_to_array(
                new TokenEscapeStream(
                    $tokens,
                    TokenEscapeStreamTest_Token::String,
                    TokenEscapeStreamTest_Token::Backslash,
                ),
                false,
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenEscapeStreamTest_Token: string {
    case String    = '';
    case Slash     = '/';
    case Asterisk  = '*';
    case Backslash = '\\';
}
