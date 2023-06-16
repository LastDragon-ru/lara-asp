<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\DocumentNode;
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
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\Directive;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\DirectiveDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\Document;
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
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\Unsupported;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 */
class Factory {
    public static function create(Context $context, int $level, int $used, object $definition): Block {
        return match (true) {
            $definition instanceof ObjectTypeDefinitionNode,
            $definition instanceof ObjectType
                => new ObjectTypeDefinition($context, $level, $used, $definition),
            $definition instanceof ObjectTypeExtensionNode
                => new ObjectTypeExtension($context, $level, $used, $definition),
            $definition instanceof InterfaceTypeDefinitionNode,
            $definition instanceof InterfaceType
                => new InterfaceTypeDefinition($context, $level, $used, $definition),
            $definition instanceof InterfaceTypeExtensionNode
                => new InterfaceTypeExtension($context, $level, $used, $definition),
            $definition instanceof FieldDefinitionNode,
            $definition instanceof GraphQLFieldDefinition
                => new FieldDefinition($context, $level, $used, $definition),
            $definition instanceof InputObjectTypeDefinitionNode,
            $definition instanceof InputObjectType
                => new InputObjectTypeDefinition($context, $level, $used, $definition),
            $definition instanceof InputObjectTypeExtensionNode
                => new InputObjectTypeExtension($context, $level, $used, $definition),
            $definition instanceof InputValueDefinitionNode,
            $definition instanceof GraphQLArgument,
            $definition instanceof InputObjectField
                => new InputValueDefinition($context, $level, $used, $definition),
            $definition instanceof ScalarTypeDefinitionNode,
            $definition instanceof ScalarType
                => new ScalarTypeDefinition($context, $level, $used, $definition),
            $definition instanceof ScalarTypeExtensionNode
                => new ScalarTypeExtension($context, $level, $used, $definition),
            $definition instanceof UnionTypeDefinitionNode,
            $definition instanceof UnionType
                => new UnionTypeDefinition($context, $level, $used, $definition),
            $definition instanceof UnionTypeExtensionNode
                => new UnionTypeExtension($context, $level, $used, $definition),
            $definition instanceof EnumTypeDefinitionNode,
            $definition instanceof EnumType
                => new EnumTypeDefinition($context, $level, $used, $definition),
            $definition instanceof EnumTypeExtensionNode
                => new EnumTypeExtension($context, $level, $used, $definition),
            $definition instanceof EnumValueDefinitionNode,
            $definition instanceof GraphQLEnumValueDefinition
                => new EnumValueDefinition($context, $level, $used, $definition),
            $definition instanceof DirectiveDefinitionNode,
            $definition instanceof GraphQLDirective
                => new DirectiveDefinition($context, $level, $used, $definition),
            $definition instanceof DirectiveNode
                => new Directive($context, $level, $used, $definition),
            $definition instanceof SchemaDefinitionNode,
            $definition instanceof Schema
                => new SchemaDefinition($context, $level, $used, $definition),
            $definition instanceof SchemaExtensionNode
                => new SchemaExtension($context, $level, $used, $definition),
            $definition instanceof TypeNode && $definition instanceof Node
                => new Type($context, $level, $used, $definition),
            $definition instanceof ValueNode && $definition instanceof Node
                => new Value($context, $level, $used, $definition),
            $definition instanceof DocumentNode
                => new Document($context, $level, $used, $definition),
            default
                => throw new Unsupported($definition),
        };
    }
}
