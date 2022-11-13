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
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\PropertyOperator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class Clause extends InputObject {
    public static function getTypeName(BuilderInfo $builder, string $type = null, bool $nullable = null): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();

        return "{$directiveName}{$builderName}Clause{$type}";
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

    protected function isFieldConvertable(
        Manipulator $manipulator,
        FieldDefinition|InputValueDefinitionNode|InputObjectField|FieldDefinitionNode $field,
    ): bool {
        // Parent?
        if (!parent::isFieldConvertable($manipulator, $field)) {
            return false;
        }

        // Convertable?
        if ($manipulator->isList($field)) {
            return false;
        }

        // Resolver?
        if ($manipulator->getNodeDirective($field, FieldResolver::class)) {
            return false;
        }

        // Ignored?
        if ($manipulator->getNodeDirective($field, Ignored::class)) {
            return false;
        }

        // Ok
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getFieldOperator(
        Manipulator $manipulator,
        FieldDefinition|InputValueDefinitionNode|InputObjectField|FieldDefinitionNode $field,
        Type|TypeDefinitionNode $fieldType,
    ): ?array {
        $type     = $manipulator->getNodeName($fieldType);
        $operator = null;
        $isNested = $fieldType instanceof InputObjectTypeDefinitionNode
            || $fieldType instanceof ObjectTypeDefinitionNode
            || $fieldType instanceof InputObjectType
            || $fieldType instanceof ObjectType;

        if ($isNested) {
            $operator = $this->getObjectDefaultOperator($manipulator, $field, $fieldType);
        } else {
            $type     = $manipulator->getType(Direction::class);
            $operator = $manipulator->getOperator(PropertyOperator::class);
        }

        return [$operator, $type];
    }

    protected function getObjectDefaultOperator(
        Manipulator $manipulator,
        InputValueDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition $field,
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $fieldType,
    ): OperatorContract {
        return parent::getFieldDirectiveOperator(Operator::class, $manipulator, $field, $fieldType)
            ?? $manipulator->getOperator(Property::class);
    }
}
