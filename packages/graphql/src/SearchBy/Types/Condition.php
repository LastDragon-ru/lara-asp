<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

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
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorPropertyDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use Override;

use function array_merge;
use function array_unique;
use function array_values;
use function is_string;

use const SORT_REGULAR;

class Condition extends InputObject {
    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        $typeName      = $source->getTypeName();
        $builderName   = $context->get(HandlerContextBuilderInfo::class)?->value->getName() ?? 'Unknown';
        $directiveName = Directive::Name;

        return "{$directiveName}{$builderName}Condition{$typeName}";
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
        return "Available conditions for `{$source}` (only one property allowed at a time).";
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
        return array_values(array_unique(
            array_merge(
                parent::getOperators($manipulator, $source, $context),
                $manipulator->getTypeOperators($this->getScope(), Operators::Extra, $context),
            ),
            SORT_REGULAR,
        ));
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
            $operator = $manipulator->getOperator($this->getScope(), SearchByOperatorPropertyDirective::class);
            $operator = [$operator, $source];
        }

        return $operator;
    }
}
