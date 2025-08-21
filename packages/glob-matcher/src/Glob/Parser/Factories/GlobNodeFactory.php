<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\DiyParser\Ast\NodeParentFactory;
use LastDragon_ru\GlobMatcher\Glob\Ast\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\GlobNodeChild;
use Override;

/**
 * @extends NodeParentFactory<GlobNode, GlobNodeChild>
 */
class GlobNodeFactory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new GlobNode($children) : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
