<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorFieldDirective;
use Override;

use function array_merge;

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
        return array_merge(
            parent::getOperators($manipulator, $source, $context),
            [
                $manipulator->getOperator($this->getScope(), SortByOperatorFieldDirective::class),
            ],
        );
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
