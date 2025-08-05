<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\AST\SchemaExtensionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\AST\VariableDefinitionNode;
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
use LastDragon_ru\GraphQLPrinter\Blocks\Document\Directive;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\DirectiveDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\Document;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\EnumTypeDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\EnumTypeExtension;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\EnumValueDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\Field;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\FieldDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\FragmentDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\FragmentSpread;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\InlineFragment;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\InputObjectTypeDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\InputObjectTypeExtension;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\InputValueDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\InterfaceTypeDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\InterfaceTypeExtension;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\ObjectTypeDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\ObjectTypeExtension;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\OperationDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\ScalarTypeDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\ScalarTypeExtension;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\SchemaDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\SchemaExtension;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\SelectionSet;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\Type;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\UnionTypeDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\UnionTypeExtension;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\Value;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\VariableDefinition;
use LastDragon_ru\GraphQLPrinter\Exceptions\Unsupported;
use LastDragon_ru\GraphQLPrinter\Misc\Context;

/**
 * @internal
 */
class Factory {
    public static function create(Context $context, object $definition, (TypeNode&Node)|GraphQLType|null $type): Block {
        return match (true) {
            $definition instanceof ObjectTypeDefinitionNode,
            $definition instanceof ObjectType
                => new ObjectTypeDefinition($context, $definition),
            $definition instanceof ObjectTypeExtensionNode
                => new ObjectTypeExtension($context, $definition),
            $definition instanceof InterfaceTypeDefinitionNode,
            $definition instanceof InterfaceType
                => new InterfaceTypeDefinition($context, $definition),
            $definition instanceof InterfaceTypeExtensionNode
                => new InterfaceTypeExtension($context, $definition),
            $definition instanceof FieldDefinitionNode,
            $definition instanceof GraphQLFieldDefinition
                => new FieldDefinition($context, $definition),
            $definition instanceof InputObjectTypeDefinitionNode,
            $definition instanceof InputObjectType
                => new InputObjectTypeDefinition($context, $definition),
            $definition instanceof InputObjectTypeExtensionNode
                => new InputObjectTypeExtension($context, $definition),
            $definition instanceof InputValueDefinitionNode,
            $definition instanceof GraphQLArgument,
            $definition instanceof InputObjectField
                => new InputValueDefinition($context, $definition),
            $definition instanceof ScalarTypeDefinitionNode,
            $definition instanceof ScalarType
                => new ScalarTypeDefinition($context, $definition),
            $definition instanceof ScalarTypeExtensionNode
                => new ScalarTypeExtension($context, $definition),
            $definition instanceof UnionTypeDefinitionNode,
            $definition instanceof UnionType
                => new UnionTypeDefinition($context, $definition),
            $definition instanceof UnionTypeExtensionNode
                => new UnionTypeExtension($context, $definition),
            $definition instanceof EnumTypeDefinitionNode,
            $definition instanceof EnumType
                => new EnumTypeDefinition($context, $definition),
            $definition instanceof EnumTypeExtensionNode
                => new EnumTypeExtension($context, $definition),
            $definition instanceof EnumValueDefinitionNode,
            $definition instanceof GraphQLEnumValueDefinition
                => new EnumValueDefinition($context, $definition),
            $definition instanceof DirectiveDefinitionNode,
            $definition instanceof GraphQLDirective
                => new DirectiveDefinition($context, $definition),
            $definition instanceof DirectiveNode
                => new Directive($context, $definition),
            $definition instanceof SchemaDefinitionNode,
            $definition instanceof Schema
                => new SchemaDefinition($context, $definition),
            $definition instanceof SchemaExtensionNode
                => new SchemaExtension($context, $definition),
            $definition instanceof TypeNode && $definition instanceof Node
                => new Type($context, $definition),
            $definition instanceof ValueNode && $definition instanceof Node
                => new Value($context, $definition, $type),
            $definition instanceof DocumentNode
                => new Document($context, $definition),
            $definition instanceof FieldNode
                => new Field($context, $definition, $type),
            $definition instanceof SelectionSetNode
                => new SelectionSet($context, $definition, $type),
            $definition instanceof InlineFragmentNode
                => new InlineFragment($context, $definition, $type),
            $definition instanceof VariableDefinitionNode
                => new VariableDefinition($context, $definition),
            $definition instanceof FragmentDefinitionNode
                => new FragmentDefinition($context, $definition),
            $definition instanceof FragmentSpreadNode
                => new FragmentSpread($context, $definition, $type),
            $definition instanceof OperationDefinitionNode
                => new OperationDefinition($context, $definition, $type),
            default
                => throw new Unsupported($definition),
        };
    }
}
