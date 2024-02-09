<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition;

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
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorConditionDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Enumeration;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Scalar;
use Override;
use ReflectionClass;

use function is_string;

abstract class Type extends InputObject {
    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        $name          = (new ReflectionClass($this))->getShortName();
        $typeName      = $source->getTypeName();
        $builderName   = $context->get(HandlerContextBuilderInfo::class)?->value->getName() ?? 'Unknown';
        $directiveName = Directive::Name;

        return "{$directiveName}{$builderName}{$name}{$typeName}";
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
        return "Available conditions for `{$source}` (only one field allowed at a time).";
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
            $field->isScalar() => Scalar::class,
            $field->isEnum()   => Enumeration::class,
            $field->isObject() => parent::getFieldOperator($manipulator, $field, $context),
            default            => throw new NotImplemented($field),
        };

        if (is_string($operator)) {
            $type     = $manipulator->getType($operator, $field, $context);
            $source   = $manipulator->getTypeSource(Parser::typeReference($type));
            $operator = SearchByOperatorConditionDirective::class;
            $operator = $manipulator->getOperator($this->getScope(), $operator, $context);
            $operator = $operator ? [$operator, $source] : null;
        }

        return $operator;
    }
}
