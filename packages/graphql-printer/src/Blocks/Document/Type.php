<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\Unsupported;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 */
#[GraphQLAstNode(NamedTypeNode::class)]
#[GraphQLAstNode(ListTypeNode::class)]
#[GraphQLAstNode(NonNullTypeNode::class)]
#[GraphQLDefinition(ListOfType::class)]
#[GraphQLDefinition(NonNull::class)]
class Type extends Block implements NamedBlock {
    /**
     * @param (TypeNode&Node)|(GraphQLType&(OutputType|InputType)) $definition
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        private TypeNode|GraphQLType $definition,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        return $this->getTypeName($this->getDefinition());
    }

    /**
     * @return (TypeNode&Node)|(GraphQLType&(OutputType|InputType))
     */
    protected function getDefinition(): TypeNode|GraphQLType {
        return $this->definition;
    }

    protected function content(): string {
        $definition = $this->getDefinition();
        $name       = $this->getName();
        $type       = '';

        if ($this->isTypeAllowed($name)) {
            $type = $this->serialize($definition);

            $this->addUsedType($name);
        }

        return $type;
    }

    /**
     * @param (TypeNode&Node)|GraphQLType $definition
     */
    private function serialize(Node|GraphQLType $definition): string {
        return match (true) {
            $definition instanceof NameNode        => $definition->value,
            $definition instanceof NamedTypeNode   => $this->serialize($definition->name),
            $definition instanceof NonNullTypeNode => $this->nonNull($this->serialize($definition->type)),
            $definition instanceof ListTypeNode    => $this->list($this->serialize($definition->type)),
            $definition instanceof NamedType       => $definition->name(),
            $definition instanceof NonNull         => $this->nonNull($this->serialize($definition->getWrappedType())),
            $definition instanceof ListOfType      => $this->list($this->serialize($definition->getWrappedType())),
            default                                => throw new Unsupported($definition),
        };
    }

    private function list(string $type): string {
        return "[{$type}]";
    }

    private function nonNull(string $type): string {
        return "{$type}!";
    }
}
