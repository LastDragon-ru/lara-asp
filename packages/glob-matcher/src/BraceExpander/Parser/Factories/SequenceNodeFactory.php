<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser\Factories;

use LastDragon_ru\DiyParser\Ast\NodeParentFactory;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\SequenceNodeChild;
use Override;

use function count;

/**
 * @extends NodeParentFactory<SequenceNode, SequenceNodeChild>
 */
class SequenceNodeFactory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return count($children) > 1 ? new SequenceNode($children) : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
