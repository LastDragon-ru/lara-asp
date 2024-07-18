<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

/**
 * @internal
 */
class TokenList {
    public function __construct(
        /**
         * @var array<string, Token<*>>
         */
        public readonly array $tokens,
    ) {
        // empty
    }
}
