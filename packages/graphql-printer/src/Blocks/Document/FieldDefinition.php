<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 *
 * @extends DefinitionBlock<FieldDefinitionNode|GraphQLFieldDefinition>
 */
#[GraphQLAstNode(FieldDefinitionNode::class)]
#[GraphQLDefinition(GraphQLFieldDefinition::class)]
class FieldDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        FieldDefinitionNode|GraphQLFieldDefinition $definition,
    ) {
        parent::__construct($context, $definition);
    }

    protected function content(Collector $collector, int $level, int $used): string {
        return $this->isTypeAllowed($this->getType())
            ? parent::content($collector, $level, $used)
            : '';
    }

    protected function type(int $level, int $used, bool $multiline): ?Block {
        return new Type(
            $this->getContext(),
            $this->getType(),
        );
    }

    protected function arguments(int $level, int $used, bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $arguments  = new ArgumentsDefinition(
            $this->getContext(),
            $definition instanceof FieldDefinitionNode
                ? $definition->arguments
                : $definition->args,
        );

        return $arguments;
    }

    /**
     * @return (TypeNode&Node)|(GraphQLType&(OutputType|InputType))
     */
    private function getType(): TypeNode|GraphQLType {
        $definition = $this->getDefinition();
        $type       = $definition instanceof FieldDefinitionNode
            ? $definition->type
            : $definition->getType();

        return $type;
    }
}
