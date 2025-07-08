<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Parser\Factories;

use LastDragon_ru\DiyParser\Ast\NodeParentFactory;
use LastDragon_ru\GlobMatcher\Ast\Nodes\NameNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\NameNodeChild;
use Override;

/**
 * @extends NodeParentFactory<NameNode, NameNodeChild>
 */
class NameNodeFactory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new NameNode($children) : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
