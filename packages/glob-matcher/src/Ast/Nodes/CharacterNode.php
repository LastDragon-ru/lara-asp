<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\GlobMatcher\Ast\ParentNode;

/**
 * @extends ParentNode<CharacterNodeChild>
 */
class CharacterNode extends ParentNode implements NameNodeChild {
    /**
     * @param list<CharacterNodeChild> $children
     */
    public function __construct(
        public bool $negated,
        array $children,
    ) {
        parent::__construct($children);
    }
}
