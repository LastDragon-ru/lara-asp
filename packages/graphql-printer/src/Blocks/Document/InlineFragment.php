<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<InlineFragmentNode>
 */
#[GraphQLAstNode(InlineFragmentNode::class)]
class InlineFragment extends DefinitionBlock implements ExecutableDefinitionBlock {
    public function __construct(
        Context $context,
        InlineFragmentNode $definition,
        private (TypeNode&Node)|Type|null $type,
    ) {
        parent::__construct($context, $definition);

        $this->type = $definition->typeCondition ?? $this->type;
    }

    #[Override]
    public function getName(): string {
        $name = '...';
        $type = $this->getDefinition()->typeCondition->name->value ?? null;

        if ($type !== null) {
            $space = $this->space();
            $name  = "{$name}{$space}on{$space}{$type}";
        }

        return $name;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        if (!$this->isTypeAllowed($this->type)) {
            return '';
        }

        // Convert
        $content = parent::content($collector, $level, $used);

        // Statistics
        if ($content !== '' && $this->type !== null) {
            $collector->addUsedType($this->getTypeName($this->type));
        }

        // Return
        return $content;
    }

    #[Override]
    protected function fields(bool $multiline): ?Block {
        return new SelectionSet(
            $this->getContext(),
            $this->getDefinition()->selectionSet,
            $this->type,
        );
    }
}
