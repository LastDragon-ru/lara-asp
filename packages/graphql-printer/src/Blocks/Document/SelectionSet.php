<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\SelectionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Exceptions\Unsupported;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 * @extends ListBlock<Block, array-key, SelectionNode&Node>
 */
#[GraphQLAstNode(SelectionSetNode::class)]
class SelectionSet extends ListBlock implements ExecutableDefinitionBlock {
    public function __construct(
        Context $context,
        SelectionSetNode $definition,
        private (TypeNode&Node)|Type|null $type,
    ) {
        parent::__construct($context, $definition->selections);
    }

    #[Override]
    protected function getPrefix(): string {
        return '{';
    }

    #[Override]
    protected function getSuffix(): string {
        return '}';
    }

    #[Override]
    protected function isWrapped(): bool {
        return true;
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeFields();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return true;
    }

    #[Override]
    protected function block(string|int $key, mixed $item): Block {
        return match (true) {
            $item instanceof FieldNode          => new Field($this->getContext(), $item, $this->type),
            $item instanceof FragmentSpreadNode => new FragmentSpread($this->getContext(), $item, $this->type),
            $item instanceof InlineFragmentNode => new InlineFragment($this->getContext(), $item, $this->type),
            default                             => throw new Unsupported($item),
        };
    }
}
