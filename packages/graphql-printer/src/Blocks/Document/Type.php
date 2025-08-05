<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

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
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\GraphQLPrinter\Exceptions\Unsupported;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLDefinition;
use Override;

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
     * @param (TypeNode&Node)|(GraphQLType&InputType)|(GraphQLType&OutputType) $definition fixme(phpcs): https://github.com/slevomat/coding-standard/issues/1690
     */
    public function __construct(
        Context $context,
        private (TypeNode&Node)|(GraphQLType&OutputType)|(GraphQLType&InputType) $definition,
    ) {
        parent::__construct($context);
    }

    #[Override]
    public function getName(): string {
        return $this->getTypeName($this->getDefinition());
    }

    protected function getDefinition(): (TypeNode&Node)|(GraphQLType&OutputType)|(GraphQLType&InputType) {
        return $this->definition;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        $definition = $this->getDefinition();
        $name       = $this->getName();
        $type       = '';

        if ($this->isTypeAllowed($name)) {
            $type = $this->name($definition);

            $collector->addUsedType($name);
        }

        return $type;
    }

    private function name((TypeNode&Node)|GraphQLType $definition): string {
        return match (true) {
            $definition instanceof NameNode        => $definition->value,
            $definition instanceof NamedTypeNode   => $this->name($definition->name),
            $definition instanceof NonNullTypeNode => $this->nonNull($this->name($definition->type)),
            $definition instanceof ListTypeNode    => $this->list($this->name($definition->type)),
            $definition instanceof NamedType       => $definition->name(),
            $definition instanceof NonNull         => $this->nonNull($this->name($definition->getWrappedType())),
            $definition instanceof ListOfType      => $this->list($this->name($definition->getWrappedType())),
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
