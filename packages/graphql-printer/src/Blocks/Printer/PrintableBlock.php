<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\AST\SchemaExtensionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Type\Definition\Argument as GraphQLArgument;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\Directive;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\DirectiveDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\EnumTypeDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\EnumTypeExtension;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\EnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\FieldDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\InputObjectTypeDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\InputObjectTypeExtension;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\InputValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\InterfaceTypeDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\InterfaceTypeExtension;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\ObjectTypeDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\ObjectTypeExtension;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\ScalarTypeDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\ScalarTypeExtension;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\SchemaDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\SchemaExtension;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\UnionTypeDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\UnionTypeExtension;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\Value;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\Unsupported;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 *
 * @template TDefinition of Node|GraphQLType|GraphQLDirective|GraphQLFieldDefinition|GraphQLArgument|GraphQLEnumValueDefinition|InputObjectField|Schema
 */
class PrintableBlock extends Block implements NamedBlock {
    private Block $block;

    /**
     * @param TDefinition $definition
     */
    public function __construct(
        Context $context,
        int $level,
        private object $definition,
    ) {
        parent::__construct($context, $level);

        $this->block = $this->getDefinitionBlock($definition);
    }

    public function getName(): string {
        $name  = '';
        $block = $this->getBlock();

        if ($block instanceof NamedBlock) {
            $name = $block->getName();
        }

        return $name;
    }

    /**
     * @return TDefinition
     */
    public function getDefinition(): object {
        return $this->definition;
    }

    protected function getBlock(): Block {
        return $this->block;
    }

    protected function content(): string {
        return (string) $this->addUsed($this->getBlock());
    }

    /**
     * @param TDefinition $definition
     */
    private function getDefinitionBlock(object $definition): Block {
        $block = null;

        if ($definition instanceof ObjectTypeDefinitionNode || $definition instanceof ObjectType) {
            $block = new ObjectTypeDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof ObjectTypeExtensionNode) {
            $block = new ObjectTypeExtension(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof InterfaceTypeDefinitionNode || $definition instanceof InterfaceType) {
            $block = new InterfaceTypeDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof InterfaceTypeExtensionNode) {
            $block = new InterfaceTypeExtension(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof FieldDefinitionNode || $definition instanceof GraphQLFieldDefinition) {
            $block = new FieldDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof InputObjectTypeDefinitionNode || $definition instanceof InputObjectType) {
            $block = new InputObjectTypeDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof InputObjectTypeExtensionNode) {
            $block = new InputObjectTypeExtension(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif (
            $definition instanceof InputValueDefinitionNode
            || $definition instanceof GraphQLArgument
            || $definition instanceof InputObjectField
        ) {
            $block = new InputValueDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof ScalarTypeDefinitionNode || $definition instanceof ScalarType) {
            $block = new ScalarTypeDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof ScalarTypeExtensionNode) {
            $block = new ScalarTypeExtension(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof UnionTypeDefinitionNode || $definition instanceof UnionType) {
            $block = new UnionTypeDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof UnionTypeExtensionNode) {
            $block = new UnionTypeExtension(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof EnumTypeDefinitionNode || $definition instanceof EnumType) {
            $block = new EnumTypeDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof EnumTypeExtensionNode) {
            $block = new EnumTypeExtension(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof EnumValueDefinitionNode || $definition instanceof GraphQLEnumValueDefinition) {
            $block = new EnumValueDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof DirectiveDefinitionNode || $definition instanceof GraphQLDirective) {
            $block = new DirectiveDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof DirectiveNode) {
            $block = new Directive(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof SchemaDefinitionNode || $definition instanceof Schema) {
            $block = new SchemaDefinition(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof SchemaExtensionNode) {
            $block = new SchemaExtension(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof TypeNode && $definition instanceof Node) {
            $block = new Type(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof ValueNode && $definition instanceof Node) {
            $block = new Value(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } else {
            throw new Unsupported($definition);
        }

        return $block;
    }
}
