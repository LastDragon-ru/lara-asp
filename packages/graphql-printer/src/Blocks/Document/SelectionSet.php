<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\SelectionNode;
use GraphQL\Language\AST\SelectionSetNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\Unsupported;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 * @extends ListBlock<Block, array-key, SelectionNode&Node>
 */
#[GraphQLAstNode(SelectionSetNode::class)]
class SelectionSet extends ListBlock implements ExecutableDefinitionBlock {
    public function __construct(
        Context $context,
        SelectionSetNode $definition,
    ) {
        parent::__construct($context, $definition->selections);
    }

    protected function getPrefix(): string {
        return '{';
    }

    protected function getSuffix(): string {
        return '}';
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeFields();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function block(string|int $key, mixed $item): Block {
        return match (true) {
            $item instanceof FieldNode => new Field($this->getContext(), $item),
            default                    => throw new Unsupported($item),
        };
    }
}
