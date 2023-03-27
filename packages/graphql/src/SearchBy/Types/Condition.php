<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
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
use LastDragon_ru\LaraASP\GraphQL\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Property;

use function is_string;

class Condition extends InputObject {
    public static function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        $typeName      = $source->getTypeName();
        $builderName   = $builder->getName();
        $directiveName = Directive::Name;

        return "{$directiveName}{$builderName}Condition{$typeName}";
    }

    protected function getScope(): string {
        return Directive::getScope();
    }

    protected function getDescription(
        Manipulator $manipulator,
        InputSource|ObjectSource|InterfaceSource $source,
    ): string {
        return "Available conditions for `{$source}` (only one property allowed at a time).";
    }

    /**
     * @inheritDoc
     */
    protected function getOperators(
        Manipulator $manipulator,
        InputSource|ObjectSource|InterfaceSource $source,
    ): array {
        return $manipulator->getTypeOperators($this->getScope(), Operators::Extra);
    }

    protected function isFieldConvertable(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
    ): bool {
        // Parent?
        if (!parent::isFieldConvertable($manipulator, $field)) {
            return false;
        }

        // Ignored field?
        if ($manipulator->getNodeDirective($field->getField(), Ignored::class) !== null) {
            return false;
        }

        // Ignored type?
        $fieldType = $field->getTypeDefinition();

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
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
    ): ?array {
        $fieldType = $field->getTypeDefinition();
        $operator  = match (true) {
            $fieldType instanceof ScalarTypeDefinitionNode,
                $fieldType instanceof ScalarType
                    => Scalar::class,
            $fieldType instanceof EnumTypeDefinitionNode,
                $fieldType instanceof EnumType
                    => Enumeration::class,
            $fieldType instanceof InputObjectTypeDefinitionNode,
                $fieldType instanceof ObjectTypeDefinitionNode,
                $fieldType instanceof InputObjectType,
                $fieldType instanceof ObjectType,
                $fieldType instanceof InterfaceTypeDefinitionNode,
                $fieldType instanceof InterfaceType
                    => $this->getObjectDefaultOperator($manipulator, $field),
            default
                    => null,
        };

        if (!$operator) {
            throw new NotImplemented($field);
        }

        // Create input
        $source = null;

        if (is_string($operator)) {
            $type     = $manipulator->getType($operator, $field);
            $source   = $manipulator->getTypeSource(Parser::typeReference($type));
            $operator = $manipulator->getOperator($this->getScope(), Property::class);
        }

        return [$operator, $source];
    }

    protected function getObjectDefaultOperator(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
    ): ?OperatorContract {
        // Directive?
        $directive = parent::getFieldDirectiveOperator(Operator::class, $manipulator, $field);

        if ($directive) {
            return $directive;
        }

        // Condition
        $builder   = $manipulator->getBuilderInfo()->getBuilder();
        $operators = $manipulator->getTypeOperators($this->getScope(), Operators::Condition);
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
