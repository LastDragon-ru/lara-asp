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
        int $level,
        int $used,
        FieldDefinitionNode|GraphQLFieldDefinition $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function content(): string {
        return $this->isTypeAllowed($this->getType())
            ? parent::content()
            : '';
    }

    protected function body(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $type       = $this->addUsed(
            new Type(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $this->getType(),
            ),
        );
        $args       = $this->addUsed(
            new ArgumentsDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition instanceof FieldDefinitionNode
                    ? $definition->arguments
                    : $definition->args,
            ),
        );

        return "{$args}:{$space}{$type}";
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
