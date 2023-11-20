<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\VariableDefinitionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<VariableDefinitionNode>
 */
#[GraphQLAstNode(VariableDefinitionNode::class)]
class VariableDefinition extends DefinitionBlock implements ExecutableDefinitionBlock {
    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        return $this->isTypeAllowed($this->getTypeName($this->getDefinition()->type))
            ? parent::content($collector, $level, $used)
            : '';
    }

    #[Override]
    protected function name(): string {
        return '$'.parent::name();
    }

    #[Override]
    protected function type(bool $multiline): ?Block {
        return new Type($this->getContext(), $this->getDefinition()->type);
    }

    #[Override]
    protected function value(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $value      = $definition->defaultValue
            ? new Value($this->getContext(), $definition->defaultValue, $definition->type)
            : null;

        return $value;
    }
}
