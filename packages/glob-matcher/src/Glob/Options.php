<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

use LastDragon_ru\GlobMatcher\MatchMode;

readonly class Options {
    public function __construct(
        /**
         * If set, the `**` will match all files and zero or more directories
         * and subdirectories.
         *
         * The same as `globstar`.
         */
        public bool $globstar = true,
        /**
         * Enables extended globbing (`?(pattern-list)`, etc).
         *
         * The same as `extglob`.
         */
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
