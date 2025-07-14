<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Iterables;

use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TokenUnescapeIterable::class)]
final class TokenUnescapeIterableTest extends TestCase {
    public function testGetIterator(): void {
        $tokens = [
            new Token(TokenUnescapeIterableTest_Token::String, 'a', 0),
            new Token(TokenUnescapeIterableTest_Token::Asterisk, '*', 1),
            new Token(TokenUnescapeIterableTest_Token::Backslash, '\\', 2),
            new Token(TokenUnescapeIterableTest_Token::Asterisk, '*', 3),
            new Token(TokenUnescapeIterableTest_Token::String, 'b', 4),
            new Token(TokenUnescapeIterableTest_Token::Backslash, '\\', 5),
            new Token(TokenUnescapeIterableTest_Token::Backslash, '\\', 6),
            new Token(TokenUnescapeIterableTest_Token::Slash, '/', 7),
            new Token(TokenUnescapeIterableTest_Token::Backslash, '\\', 8),
            new Token(TokenUnescapeIterableTest_Token::String, 'c', 9),
            new Token(TokenUnescapeIterableTest_Token::Backslash, '\\', 10),
        ];

        self::assertEquals(
            [
                new Token(TokenUnescapeIterableTest_Token::String, 'a', 0),
                new Token(TokenUnescapeIterableTest_Token::Asterisk, '*', 1),
                new Token(TokenUnescapeIterableTest_Token::String, '*', 3),
                new Token(TokenUnescapeIterableTest_Token::String, 'b', 4),
                new Token(TokenUnescapeIterableTest_Token::String, '\\', 6),
                new Token(TokenUnescapeIterableTest_Token::Slash, '/', 7),
                new Token(TokenUnescapeIterableTest_Token::String, 'c', 9),
            ],
            iterator_to_array(
                new TokenUnescapeIterable(
                    $tokens,
                    TokenUnescapeIterableTest_Token::String,
                    TokenUnescapeIterableTest_Token::Backslash,
                ),
                false,
            ),
        );
    }

    public function testGetIteratorUnescapable(): void {
        $tokens   = [
            new Token(TokenUnescapeIterableTest_Token::String, 'a', 0),
            new Token(TokenUnescapeIterableTest_Token::Asterisk, '*', 1),
            new Token(TokenUnescapeIterableTest_Token::Backslash, '\\', 2),
            new Token(TokenUnescapeIterableTest_Token::Asterisk, '*', 3),
            new Token(TokenUnescapeIterableTest_Token::Backslash, '\\', 4),
            new Token(TokenUnescapeIterableTest_Token::Slash, '/', 5),
        ];
        $iterable = new readonly class(
            $tokens,
            TokenUnescapeIterableTest_Token::String,
            TokenUnescapeIterableTest_Token::Backslash,
        ) extends TokenUnescapeIterable {
            #[Override]
            protected function isEscapable(Token $token): bool {
                return parent::isEscapable($token)
                    && $token->name !== TokenUnescapeIterableTest_Token::Asterisk;
            }
        };

        self::assertEquals(
            [
                new Token(TokenUnescapeIterableTest_Token::String, 'a', 0),
                new Token(TokenUnescapeIterableTest_Token::Asterisk, '*', 1),
                new Token(TokenUnescapeIterableTest_Token::Asterisk, '*', 3),
                new Token(TokenUnescapeIterableTest_Token::String, '/', 5),
            ],
            iterator_to_array($iterable, false),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenUnescapeIterableTest_Token: string {
    case String    = '';
    case Asterisk  = '*';
    case Slash     = '/';
    case Backslash = '\\';
}
