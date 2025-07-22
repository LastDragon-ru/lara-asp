<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

class IntegerSequenceNode extends IncrementalSequenceNode {
    public function __construct(
        /**
         * @var numeric-string
         */
        public string $start,
        /**
         * @var numeric-string
         */
        public string $end,
        public ?int $increment = null,
    ) {
        // empty
    }
}
