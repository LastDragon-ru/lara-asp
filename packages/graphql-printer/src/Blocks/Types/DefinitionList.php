<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use Closure;
use GraphQL\Language\AST\DefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Factory;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 * @extends ListBlock<Block, array-key, DefinitionNode&Node>
 */
class DefinitionList extends ListBlock {
    /**
     * @param iterable<array-key, DefinitionNode&Node>                  $items
     * @param Closure(DefinitionNode&Node): ((TypeNode&Node)|Type|null) $type
     */
    public function __construct(
        Context $context,
        iterable $items,
        private Closure $type,
    ) {
        parent::__construct($context, $items);
    }

    #[Override]
    protected function isWrapped(): bool {
        return true;
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDefinitions();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return true;
    }

    #[Override]
    protected function block(int|string $key, mixed $item): Block {
        return Factory::create($this->getContext(), $item, ($this->type)($item));
    }
}
