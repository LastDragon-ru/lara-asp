<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Types\InputObject;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Property;

use function is_string;

class Condition extends InputObject {
    public static function getTypeName(BuilderInfo $builder, ?string $type, ?bool $nullable): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();

        return "{$directiveName}{$builderName}Condition{$type}";
    }

    protected function getScope(): string {
        return Directive::class;
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
        string $type,
        ?bool $nullable,
    ): array {
        return $manipulator->getTypeOperators($this->getScope(), Operators::Extra, false);
    }

    /**
     * @inheritdoc
     */
    protected function getFieldOperator(
        Manipulator $manipulator,
        FieldDefinition|InputValueDefinitionNode|InputObjectField|FieldDefinitionNode $field,
        Type|TypeDefinitionNode $fieldType,
        ?bool $fieldNullable,
    ): ?array {
        $operator = match (true) {
            $fieldType instanceof ScalarTypeDefinitionNode,
                $fieldType instanceof ScalarType
                    => Scalar::class,
            $fieldType instanceof EnumTypeDefinitionNode,
                $fieldType instanceof EnumType
                    => Enumeration::class,
            $fieldType instanceof InputObjectTypeDefinitionNode,
                $fieldType instanceof ObjectTypeDefinitionNode,
                $fieldType instanceof InputObjectType,
                $fieldType instanceof ObjectType
                    => $this->getObjectDefaultOperator($manipulator, $field, $fieldType, $fieldNullable),
            default
                    => null,
        };

        if (!$operator) {
            throw new NotImplemented($manipulator->getNodeTypeFullName($fieldType));
        }

        // Create input
        $type = $manipulator->getNodeName($fieldType);

        if (is_string($operator)) {
            $type     = $manipulator->getType($operator, $type, $fieldNullable);
            $operator = $manipulator->getOperator($this->getScope(), Property::class);
        }

        return [$operator, $type];
    }

    protected function getObjectDefaultOperator(
        Manipulator $manipulator,
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition $field,
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $fieldType,
        ?bool $fieldNullable,
    ): ?OperatorContract {
        // Directive?
        $directive = parent::getFieldDirectiveOperator(
            Operator::class,
            $manipulator,
            $field,
            $fieldType,
            $fieldNullable,
        );

        if ($directive) {
            return $directive;
        }

        // Condition
        $builder   = $manipulator->getBuilderInfo()->getBuilder();
        $operators = $manipulator->getTypeOperators($this->getScope(), Operators::Condition, false);
        $condition = null;

        foreach ($operators as $operator) {
            if ($operator->isBuilderSupported($builder)) {
                $condition = $operator;
                break;
            }
        }

        return $condition;
    }
}
