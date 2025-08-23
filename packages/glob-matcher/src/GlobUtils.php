<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\DiyParser\Iterables\StringifyIterable;
use LastDragon_ru\DiyParser\Iterables\TokenEscapeIterable;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use LastDragon_ru\DiyParser\Tokenizer\Tokenizer;
use LastDragon_ru\DiyParser\Utils;
use LastDragon_ru\GlobMatcher\BraceExpander\Parser\Name as BraceExpanderName;
use LastDragon_ru\GlobMatcher\Glob\Parser\Name as GlobName;
use Override;

readonly class GlobUtils {
    protected function __construct() {
        // empty
    }

    public static function escape(string $pattern): string {
        $tokens   = [BraceExpanderName::class, GlobName::class];
        $iterable = (new Tokenizer($tokens))->tokenize([$pattern]);
        $iterable = new readonly class($iterable, GlobName::Backslash) extends TokenEscapeIterable {
            #[Override]
            protected function isEscapable(Token $token): bool {
                return parent::isEscapable($token)
                    && $token->name !== GlobName::Colon
                    && $token->name !== GlobName::Dot
                    && $token->name !== GlobName::Equal
                    && $token->name !== GlobName::Slash
                    && $token->name !== BraceExpanderName::Comma
                    && $token->name !== BraceExpanderName::DoubleDot;
            }
        };
        $iterable = new StringifyIterable($iterable);
        $escaped  = Utils::toString($iterable);

        return $escaped;
    }
}
