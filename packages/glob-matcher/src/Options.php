<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

readonly class Options {
    public function __construct(
        public bool $globstar = true,
    ) {
        // empty
    }
}
