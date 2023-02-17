<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Types;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Types\InputObject;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Field;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Property;

class Clause extends InputObject {
    public static function getTypeName(BuilderInfo $builder, ?string $type, ?bool $nullable): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();

        return "{$directiveName}{$builderName}Clause{$type}";
    }

    protected function getScope(): string {
        return Directive::getScope();
    }

    protected function getTypeDescription(
        Manipulator $manipulator,
        string $name,
        string $type,
        bool $nullable = null,
    ): string {
        $typeName    = $manipulator->getNodeTypeFullName($type);
        $description = "Sort clause for `{$typeName}` (only one property allowed at a time).";

        return $description;
    }

    /**
     * @inheritDoc
     */
    protected function getTypeOperators(
        Manipulator $manipulator,
        string $name,
        string $type,
        ?bool $nullable,
    ): array {
        return $manipulator->getTypeOperators($this->getScope(), Operators::Extra, false);
    }

    protected function isConvertable(
        Manipulator $manipulator,
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|TypeDefinitionNode|Type $node,
    ): bool {
        // Parent?
        if (!parent::isConvertable($manipulator, $node)) {
            return false;
        }

        // Convertable?
        if ($manipulator->isList($node)) {
            return false;
        }

        // Ignored?
        if ($node instanceof Ignored || $manipulator->getNodeDirective($node, Ignored::class) !== null) {
            return false;
        }

        // Ok
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getFieldOperator(
        Manipulator $manipulator,
        FieldDefinition|InputValueDefinitionNode|InputObjectField|FieldDefinitionNode $field,
        Type|TypeDefinitionNode $fieldType,
        ?bool $fieldNullable,
    ): ?array {
        $type     = $manipulator->getNodeName($fieldType);
        $operator = null;
        $isNested = $fieldType instanceof InputObjectTypeDefinitionNode
            || $fieldType instanceof ObjectTypeDefinitionNode
            || $fieldType instanceof InputObjectType
            || $fieldType instanceof ObjectType;

        if ($isNested) {
            $operator = $this->getObjectDefaultOperator($manipulator, $field, $fieldType, $fieldNullable);
        } else {
            $type     = $manipulator->getType(Direction::class, null, null);
            $operator = $manipulator->getOperator($this->getScope(), Field::class);
        }

        return [$operator, $type];
    }

    protected function getObjectDefaultOperator(
        Manipulator $manipulator,
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition $field,
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $fieldType,
        ?bool $fieldNullable,
    ): OperatorContract {
        return parent::getFieldDirectiveOperator(Operator::class, $manipulator, $field, $fieldType, $fieldNullable)
            ?? $manipulator->getOperator($this->getScope(), Property::class);
    }
}