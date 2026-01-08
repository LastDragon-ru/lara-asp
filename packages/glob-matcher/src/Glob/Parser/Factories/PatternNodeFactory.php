<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\NameNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\PatternNode;
use LastDragon_ru\TextParser\Ast\NodeParentFactory;
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
