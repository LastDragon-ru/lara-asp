<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser;

use LastDragon_ru\TextParser\Iterables\StringTokenizeIterable;
use LastDragon_ru\TextParser\Iterables\TokenUnescapeIterable;
use LastDragon_ru\TextParser\Tokenizer\Token;
use Override;
use Traversable;

readonly class Tokenizer {
    public function __construct() {
        // empty
    }

    /**
     * @param iterable<mixed, string> $iterable
     *
     * @return iterable<mixed, Token<Name>>
     */
    public function tokenize(iterable $iterable, int $offset = 0): iterable {
        $iterable = new StringTokenizeIterable($iterable, Name::class, offset: $offset);
        $iterable = new readonly class($iterable, Name::Backslash) extends TokenUnescapeIterable {
            #[Override]
            public function getIterator(): Traversable {
                foreach (parent::getIterator() as $key => $token) {
                    if ($token->name === $this->escape) {
                        continue;
                    }

                    yield $key => $token;
                }
            }

            #[Override]
            protected function isEscapable(Token $token): bool {
                return parent::isEscapable($token)
                    && $token->name !== Name::Slash;
            }
        };

        return $iterable;
    }
}
