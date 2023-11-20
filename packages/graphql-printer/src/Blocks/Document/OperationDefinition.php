<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<OperationDefinitionNode>
 */
#[GraphQLAstNode(OperationDefinitionNode::class)]
class OperationDefinition extends DefinitionBlock implements ExecutableDefinitionBlock {
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function __construct(
        Context $context,
        OperationDefinitionNode $definition,
        private TypeNode|Type|null $type,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return $this->getDefinition()->operation;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        $type = $this->getType();

        if (!$this->isTypeAllowed($type)) {
            return '';
        }

        // Convert
        $content = parent::content($collector, $level, $used);

        // Statistics
        if ($content && $type) {
            $collector->addUsedType($this->getTypeName($type));
        }

        // Return
        return $content;
    }

    #[Override]
    protected function arguments(bool $multiline): ?Block {
        return new VariablesDefinition(
            $this->getContext(),
            $this->getDefinition()->variableDefinitions,
        );
    }

    #[Override]
    protected function fields(bool $multiline): ?Block {
        return new SelectionSet(
            $this->getContext(),
            $this->getDefinition()->selectionSet,
            $this->getType(),
        );
    }

    /**
     * @return (TypeNode&Node)|Type|null
     */
    private function getType(): TypeNode|Type|null {
        $definition = $this->getDefinition();
        $type       = $this->type
            ?? $this->getContext()->getOperationType($definition->operation);

        return $type;
    }
}
