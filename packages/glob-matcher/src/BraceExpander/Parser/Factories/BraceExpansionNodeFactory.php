<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser\Factories;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNodeChild;
use LastDragon_ru\TextParser\Ast\NodeParentFactory;
use Override;

/**
 * @extends NodeParentFactory<BraceExpansionNode, BraceExpansionNodeChild>
 */
class BraceExpansionNodeFactory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return new BraceExpansionNode($children);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
