<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TokenUnescapeStream::class)]
final class TokenUnescapeStreamTest extends TestCase {
    public function testGetIterator(): void {
        $tokens = [
            new Token(TokenUnescapeStreamTest_Token::String, 'a', 0),
            new Token(TokenUnescapeStreamTest_Token::Asterisk, '*', 1),
            new Token(TokenUnescapeStreamTest_Token::Backslash, '\\', 2),
            new Token(TokenUnescapeStreamTest_Token::Asterisk, '*', 3),
            new Token(TokenUnescapeStreamTest_Token::String, 'b', 4),
            new Token(TokenUnescapeStreamTest_Token::Backslash, '\\', 5),
            new Token(TokenUnescapeStreamTest_Token::Backslash, '\\', 6),
            new Token(TokenUnescapeStreamTest_Token::Slash, '/', 7),
            new Token(TokenUnescapeStreamTest_Token::Backslash, '\\', 8),
            new Token(TokenUnescapeStreamTest_Token::String, 'c', 9),
            new Token(TokenUnescapeStreamTest_Token::Backslash, '\\', 10),
        ];

        self::assertEquals(
            [
                new Token(TokenUnescapeStreamTest_Token::String, 'a', 0),
                new Token(TokenUnescapeStreamTest_Token::Asterisk, '*', 1),
                new Token(TokenUnescapeStreamTest_Token::String, '*', 3),
                new Token(TokenUnescapeStreamTest_Token::String, 'b', 4),
                new Token(TokenUnescapeStreamTest_Token::String, '\\', 6),
                new Token(TokenUnescapeStreamTest_Token::Slash, '/', 7),
                new Token(TokenUnescapeStreamTest_Token::String, 'c', 9),
            ],
            iterator_to_array(
                new TokenUnescapeStream(
                    $tokens,
                    TokenUnescapeStreamTest_Token::String,
                    TokenUnescapeStreamTest_Token::Backslash,
                ),
                false,
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenUnescapeStreamTest_Token: string {
    case String    = '';
    case Asterisk  = '*';
    case Slash     = '/';
    case Backslash = '\\';
}
