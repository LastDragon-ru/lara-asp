<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

class CharacterSequenceNode extends IncrementalSequenceNode {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $start,
        /**
         * @var non-empty-string
         */
        public string $end,
        public ?int $increment = null,
    ) {
        // empty
    }
}
