<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\GlobMatcher\BraceExpander\BraceExpander;
use LastDragon_ru\GlobMatcher\Glob\Options as GlobOptions;

readonly class Options {
    public function __construct(
        /**
         * Expand Brace Expansion?
         *
         * @see BraceExpander
         */
        public bool $braces = true,
        /**
         * @see GlobOptions::$globstar
         */
        public bool $globstar = true,
        /**
         * @see GlobOptions::$extended
         */
        public bool $extended = true,
        /**
         * @see GlobOptions::$hidden
         */
        public bool $hidden = false,
        public MatchMode $matchMode = MatchMode::Match,
        public bool $matchCase = true,
    ) {
        // empty
    }
}
