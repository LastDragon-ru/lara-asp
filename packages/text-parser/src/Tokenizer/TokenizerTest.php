<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Tokenizer::class)]
final class TokenizerTest extends TestCase {
    public function testTokenize(): void {
        $source    = ['abc*cd\\*e'];
        $tokenizer = new Tokenizer(TokenizerTest_Token::class, TokenizerTest_Token::Backslash);
        $expected  = [
            new Token(null, 'abc', 0),
            new Token(TokenizerTest_Token::Asterisk, '*', 3),
            new Token(null, 'cd', 4),
            new Token(null, '*', 7),
            new Token(null, 'e', 8),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source), false));
    }

    public function testTokenizeNoEscape(): void {
        $source    = ['abc*cd\\*e'];
        $offset    = 2;
        $tokenizer = new Tokenizer(TokenizerTest_Token::class);
        $expected  = [
            new Token(null, 'abc', $offset + 0),
            new Token(TokenizerTest_Token::Asterisk, '*', $offset + 3),
            new Token(null, 'cd', $offset + 4),
            new Token(TokenizerTest_Token::Backslash, '\\', $offset + 6),
            new Token(TokenizerTest_Token::Asterisk, '*', $offset + 7),
            new Token(null, 'e', $offset + 8),
        ];

        self::assertEquals($expected, iterator_to_array($tokenizer->tokenize($source, $offset), false));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum TokenizerTest_Token: string {
    case String    = '';
    case Slash     = '/';
    case Asterisk  = '*';
    case Backslash = '\\';
}
