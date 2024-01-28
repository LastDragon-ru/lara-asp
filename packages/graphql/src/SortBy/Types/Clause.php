<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Types;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
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
use Override;

use function array_merge;
use function array_unique;
use function array_values;

use const SORT_REGULAR;

class Clause extends InputObject {
    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        $directiveName = Directive::Name;
        $builderName   = $context->get(HandlerContextBuilderInfo::class)?->value->getName() ?? 'Unknown';
        $typeName      = $source->getTypeName();

        return "{$directiveName}{$builderName}Clause{$typeName}";
    }

    #[Override]
    protected function getScope(): string {
        return Directive::getScope();
    }

    #[Override]
    protected function getDescription(
        Manipulator $manipulator,
        InputSource|ObjectSource|InterfaceSource $source,
        Context $context,
    ): string {
        return "Sort clause for `{$source}` (only one property allowed at a time).";
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getOperators(
        Manipulator $manipulator,
        InputSource|ObjectSource|InterfaceSource $source,
        Context $context,
    ): array {
        return array_values(
            array_unique(
                array_merge(
                    parent::getOperators($manipulator, $source, $context),
                    $manipulator->getTypeOperators($this->getScope(), Operators::Extra, $context),
                ),
                SORT_REGULAR,
            ),
        );
    }

    #[Override]
    protected function isFieldConvertableImplicit(
        Manipulator $manipulator,
        ObjectFieldSource|InputFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): bool {
        // Parent?
        if (!parent::isFieldConvertableImplicit($manipulator, $field, $context,)) {
            return false;
        }

        // List of scalars/enums?
        if ($field->isList() && !$field->isObject()) {
            return false;
        }

        // Ok
        return true;
    }

    #[Override]
    protected function getFieldMarkerIgnored(): ?string {
        return Ignored::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getFieldOperator(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
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
            $operator = $this->getObjectDefaultOperator($manipulator, $field, $context);
        } else {
            $type     = $manipulator->getType(Direction::class, $field, $context);
            $source   = $manipulator->getTypeSource(Parser::typeReference($type));
            $operator = $manipulator->getOperator($this->getScope(), SortByOperatorFieldDirective::class);
        }

        return [$operator, $source];
    }

    protected function getObjectDefaultOperator(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): OperatorContract {
        return parent::getFieldDirectiveOperator(Operator::class, $manipulator, $field, $context)
            ?? $manipulator->getOperator($this->getScope(), SortByOperatorPropertyDirective::class);
    }
}
