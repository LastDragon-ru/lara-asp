<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Parser\Factories;

use LastDragon_ru\DiyParser\Ast\NodeParentFactory;
use LastDragon_ru\GlobMatcher\Ast\Nodes\PatternListNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\PatternListNodeChild;
use LastDragon_ru\GlobMatcher\Ast\Nodes\PatternListQuantifier;
use Override;

/**
 * @extends NodeParentFactory<PatternListNode, PatternListNodeChild>
 */
class PatternListNodeFactory extends NodeParentFactory {
    public function __construct(
        protected PatternListQuantifier $quantifier,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return new PatternListNode($this->quantifier, $children);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
