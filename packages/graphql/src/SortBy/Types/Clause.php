<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Types;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Types\InputObject;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Field;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Property;

class Clause extends InputObject {
    public static function getTypeName(Manipulator $manipulator, BuilderInfo $builder, ?TypeSource $source): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();
        $typeName      = $source?->getTypeName();

        return "{$directiveName}{$builderName}Clause{$typeName}";
    }

    protected function getScope(): string {
        return Directive::getScope();
    }

    protected function getTypeDescription(
        Manipulator $manipulator,
        string $name,
        ObjectSource|InputSource $node,
    ): string {
        return "Sort clause for `{$node}` (only one property allowed at a time).";
    }

    /**
     * @inheritDoc
     */
    protected function getTypeOperators(
        Manipulator $manipulator,
        string $name,
        InputSource|ObjectSource $node,
    ): array {
        return $manipulator->getTypeOperators($this->getScope(), Operators::Extra);
    }

    protected function isFieldConvertable(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource $field,
    ): bool {
        // Parent?
        if (!parent::isFieldConvertable($manipulator, $field)) {
            return false;
        }

        // List?
        if ($field->isList()) {
            return false;
        }

        // Ignored field?
        if ($manipulator->getNodeDirective($field->getField(), Ignored::class) !== null) {
            return false;
        }

        // Ignored type?
        $fieldType = $manipulator->getTypeDefinitionNode($field->getType());

        if ($fieldType instanceof Ignored || $manipulator->getNodeDirective($fieldType, Ignored::class) !== null) {
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
        InputFieldSource|ObjectFieldSource $field,
    ): ?array {
        $fieldType = $manipulator->getTypeDefinitionNode($field->getType());
        $isNested  = $fieldType instanceof InputObjectTypeDefinitionNode
            || $fieldType instanceof ObjectTypeDefinitionNode
            || $fieldType instanceof InputObjectType
            || $fieldType instanceof ObjectType;
        $operator  = null;
        $source    = null;

        if ($isNested) {
            $operator = $this->getObjectDefaultOperator($manipulator, $field);
        } else {
            $type     = $manipulator->getType(Direction::class, $field);
            $source   = $manipulator->getTypeSource(Parser::typeReference($type));
            $operator = $manipulator->getOperator($this->getScope(), Field::class);
        }

        return [$operator, $source];
    }

    protected function getObjectDefaultOperator(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource $field,
    ): OperatorContract {
        return parent::getFieldDirectiveOperator(Operator::class, $manipulator, $field)
            ?? $manipulator->getOperator($this->getScope(), Property::class);
    }
}
