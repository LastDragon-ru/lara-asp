<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Types\InputObject;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Property;

use function is_string;

class Condition extends InputObject {
    public static function getTypeName(BuilderInfo $builder, string $type = null, bool $nullable = null): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();

        return "{$directiveName}{$builderName}Condition{$type}";
    }

    protected function getTypeDescription(
        Manipulator $manipulator,
        string $name,
        string $type,
        bool $nullable = null,
    ): string {
        $typeName    = $manipulator->getNodeTypeFullName($type);
        $description = "Available conditions for `{$typeName}` (only one property allowed at a time).";

        return $description;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeOperators(
        Manipulator $manipulator,
        string $name,
        string $type = null,
        bool $nullable = null,
    ): array {
        return $manipulator->getTypeOperators(Operators::Logical, false);
    }

    protected function getFieldDefinition(
        Manipulator $manipulator,
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition $field,
        TypeDefinitionNode|Type $fieldType,
    ): InputValueDefinitionNode|null {
        // Type or Operator
        $definition = match (true) {
            $fieldType instanceof ScalarTypeDefinitionNode,
                $fieldType instanceof ScalarType => Scalar::class,
            $fieldType instanceof EnumTypeDefinitionNode,
                $fieldType instanceof EnumType   => Enumeration::class,
            $fieldType instanceof InputObjectTypeDefinitionNode,
                $fieldType instanceof ObjectTypeDefinitionNode,
                $fieldType instanceof InputObjectType,
                $fieldType instanceof ObjectType => $this->getFieldOperator($manipulator, $field, $fieldType),
            default                              => null,
        };

        if (!$definition) {
            throw new NotImplemented($manipulator->getNodeTypeFullName($fieldType));
        }

        // Create input
        $name     = $manipulator->getNodeName($field);
        $type     = $manipulator->getNodeName($fieldType);
        $operator = null;

        if (is_string($definition)) {
            $operator = $manipulator->getOperator(Property::class);
            $nullable = $manipulator->isNullable($field);
            $type     = $manipulator->getType($definition, $type, $nullable);
        } else {
            $operator = $definition;
        }

        return Parser::inputValueDefinition(
            $manipulator->getOperatorField($operator, $type, $name),
        );
    }

    protected function getFieldOperator(
        Manipulator $manipulator,
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition $field,
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $fieldType,
    ): Operator {
        return parent::getFieldDirectiveOperator(Operator::class, $manipulator, $field, $fieldType)
            ?? $manipulator->getOperator(Relation::class);
    }
}
