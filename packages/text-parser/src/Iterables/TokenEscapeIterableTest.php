<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use LastDragon_ru\TextParser\Package\TestCase;
use LastDragon_ru\TextParser\Tokenizer\Token;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TokenEscapeIterable::class)]
final class TokenEscapeIterableTest extends TestCase {
    public function testGetIterator(): void {
        $tokens   = [
            new Token(null, 'a', 0),
            new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 1),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
            new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 3),
            new Token(null, 'b', 4),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 5),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 6),
            new Token(TokenEscapeIterableTest_Token::Slash, '/', 7),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 8),
            new Token(null, 'c', 9),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 10),
        ];
        $iterable = new TokenEscapeIterable(
            $tokens,
            TokenEscapeIterableTest_Token::Backslash,
        );

        self::assertEquals(
            [
                new Token(null, 'a', 0),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 1),
                new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 1),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 3),
                new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 3),
                new Token(null, 'b', 4),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 5),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 5),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 6),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 6),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 7),
                new Token(TokenEscapeIterableTest_Token::Slash, '/', 7),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 8),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 8),
                new Token(null, 'c', 9),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 10),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 10),
            ],
            iterator_to_array($iterable, false),
        );
    }

    public function testGetIteratorUnescapable(): void {
        $tokens   = [
            new Token(null, 'a', 0),
            new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 1),
            new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
            new Token(TokenEscapeIterableTest_Token::Slash, '/', 3),
        ];
        $iterable = new readonly class(
            $tokens,
            TokenEscapeIterableTest_Token::Backslash,
        ) extends TokenEscapeIterable {
            #[Override]
            protected function isEscapable(Token $token): bool {
                return parent::isEscapable($token)
                    && $token->name !== TokenEscapeIterableTest_Token::Asterisk;
            }
        };

        self::assertEquals(
            [
                new Token(null, 'a', 0),
                new Token(TokenEscapeIterableTest_Token::Asterisk, '*', 1),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 2),
                new Token(TokenEscapeIterableTest_Token::Backslash, '\\', 3),
                new Token(TokenEscapeIterableTest_Token::Slash, '/', 3),
            ],
            iterator_to_array($iterable, false),
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
