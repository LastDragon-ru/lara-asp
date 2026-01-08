<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\CharacterNodeChild;
use LastDragon_ru\TextParser\Ast\NodeParentFactory;
use Override;

/**
 * @extends NodeParentFactory<CharacterNode, CharacterNodeChild>
 */
class CharacterNodeFactory extends NodeParentFactory {
    public function __construct(
        protected bool $negated,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new CharacterNode($this->negated, $children) : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}
