<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorFieldDirective;
use Override;

class Root extends Type {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getOperators(
        Manipulator $manipulator,
        InterfaceSource|InputSource|ObjectSource $source,
        Context $context,
    ): array {
        $operators = parent::getOperators($manipulator, $source, $context);
        $operator  = $manipulator->getOperator(
            $this->getScope(),
            $source,
            $context,
            SearchByOperatorFieldDirective::class,
        );

        if ($operator) {
            $operators[] = $operator;
        }

        return $operators;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getFields(
        Manipulator $manipulator,
        InterfaceSource|InputSource|ObjectSource $source,
        Context $context,
    ): iterable {
        return [];
    }
}
