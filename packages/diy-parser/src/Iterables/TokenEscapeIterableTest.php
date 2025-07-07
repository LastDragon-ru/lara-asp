<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Iterables;

use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TokenEscapeIterable::class)]
final class TokenEscapeIterableTest extends TestCase {
    public function testGetIterator(): void {
        $tokens = [
            new Token(TokenEscapeIterableTest_Token::String, 'a', 0),
            new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 1),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
            new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 3),
            new Token(TokenEscapeIterableTest_Token::String, 'b', 4),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 5),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 6),
            new Token(TokenEscapeIterableTest_Token::Slash, '/', 7),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 8),
            new Token(TokenEscapeIterableTest_Token::String, 'c', 9),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 10),
        ];

        self::assertEquals(
            [
                new Token(TokenEscapeIterableTest_Token::String, 'a', 0),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 1),
                new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 1),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 3),
                new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 3),
                new Token(TokenEscapeIterableTest_Token::String, 'b', 4),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 5),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 5),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 6),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 6),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 7),
                new Token(TokenEscapeIterableTest_Token::Slash, '/', 7),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 8),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 8),
                new Token(TokenEscapeIterableTest_Token::String, 'c', 9),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 10),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 10),
            ],
            iterator_to_array(
                new TokenEscapeIterable(
                    $tokens,
                    TokenEscapeIterableTest_Token::String,
                    TokenEscapeIterableTest_Token::Backslash,
                ),
                false,
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenEscapeIterableTest_Token: string {
    case String    = '';
    case Slash     = '/';
    case Asterisk  = '*';
    case Backslash = '\\';
}
