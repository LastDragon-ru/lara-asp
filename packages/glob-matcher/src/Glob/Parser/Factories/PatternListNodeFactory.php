<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\DiyParser\Ast\NodeParentFactory;
use LastDragon_ru\GlobMatcher\Glob\Ast\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\PatternListNodeChild;
use LastDragon_ru\GlobMatcher\Glob\Ast\PatternListQuantifier;
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
