<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\DiyParser\Ast\NodeParentFactory;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\NameNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternNode;
use Override;

/**
 * @extends NodeParentFactory<PatternNode, NameNodeChild>
 */
class PatternNodeFactory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new PatternNode($children) : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
