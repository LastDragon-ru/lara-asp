<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Types;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Types\InputObject;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorFieldDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorPropertyDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use function array_merge;
use function array_unique;
use function array_values;

use const SORT_REGULAR;

class Clause extends InputObject {
    public function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();
        $typeName      = $source->getTypeName();

        return "{$directiveName}{$builderName}Clause{$typeName}";
    }

    protected function getScope(): string {
        return Directive::getScope();
    }

    protected function getDescription(
        Manipulator $manipulator,
        InputSource|ObjectSource|InterfaceSource $source,
    ): string {
        return "Sort clause for `{$source}` (only one property allowed at a time).";
    }

    /**
     * @inheritDoc
     */
    protected function getOperators(
        Manipulator $manipulator,
        InputSource|ObjectSource|InterfaceSource $source,
    ): array {
        return array_values(
            array_unique(
                array_merge(
                    parent::getOperators($manipulator, $source),
                    $manipulator->getTypeOperators($this->getScope(), Operators::Extra),
                ),
                SORT_REGULAR,
            ),
        );
    }

    protected function isFieldConvertable(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
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
        if ($manipulator->getDirective($field->getField(), Ignored::class) !== null) {
            return false;
        }

        // Ignored type?
        $fieldType = $field->getTypeDefinition();

        if ($fieldType instanceof Ignored || $manipulator->getDirective($fieldType, Ignored::class) !== null) {
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
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
    ): ?array {
        $fieldType = $field->getTypeDefinition();
        $isNested  = $fieldType instanceof InputObjectTypeDefinitionNode
            || $fieldType instanceof ObjectTypeDefinitionNode
            || $fieldType instanceof InputObjectType
            || $fieldType instanceof ObjectType
            || $fieldType instanceof InterfaceTypeDefinitionNode
            || $fieldType instanceof InterfaceType;
        $operator  = null;
        $source    = null;

        if ($isNested) {
            $operator = $this->getObjectDefaultOperator($manipulator, $field);
        } else {
            $type     = $manipulator->getType(Direction::class, $field);
            $source   = $manipulator->getTypeSource(Parser::typeReference($type));
            $operator = $manipulator->getOperator($this->getScope(), SortByOperatorFieldDirective::class);
        }

        return [$operator, $source];
    }

    protected function getObjectDefaultOperator(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
    ): OperatorContract {
        return parent::getFieldDirectiveOperator(Operator::class, $manipulator, $field)
            ?? $manipulator->getOperator($this->getScope(), SortByOperatorPropertyDirective::class);
    }
}
