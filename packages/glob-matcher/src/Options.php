<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

readonly class Options {
    public function __construct(
        public bool $globstar = true,
        public bool $extended = true,
        /**
         * Filenames beginning with a dot are hidden and not matched by default
         * unless the glob begins with a dot or this option set to `true`.
         *
         * The same as `dotglob`.
         */
        public bool $hidden = false,
        public MatchMode $matchMode = MatchMode::Match,
        /**
         * The same as `nocasematch`.
         */
        public bool $matchCase = true,
    ) {
        // empty
    }
}
