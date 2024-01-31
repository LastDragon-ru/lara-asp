<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Types;

use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
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
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorFieldDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use Override;

use function is_string;

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
    protected function getFieldMarkerOperator(): string {
        return Operator::class;
    }

    #[Override]
    protected function getFieldMarkerIgnored(): ?string {
        return Ignored::class;
    }

    #[Override]
    protected function getTypeForOperators(): ?string {
        return Operators::Extra;
    }

    #[Override]
    protected function getTypeForFieldOperator(): ?string {
        return Operators::Object;
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
        $operator = match (true) {
            $field->isScalar(), $field->isEnum() => Direction::class,
            $field->isObject()                   => parent::getFieldOperator($manipulator, $field, $context),
            default                              => throw new NotImplemented($field),
        };

        if (is_string($operator)) {
            $type     = $manipulator->getType($operator, $field, $context);
            $source   = $manipulator->getTypeSource(Parser::typeReference($type));
            $operator = $manipulator->getOperator($this->getScope(), SortByOperatorFieldDirective::class);
            $operator = [$operator, $source];
        } else {
            // empty
        }

        return $operator;
    }
}
