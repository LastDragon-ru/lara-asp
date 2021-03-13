<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchByDirective;

/**
 * @internal Must not be used directly.
 * @see      \LastDragon_ru\LaraASP\GraphQL\SearchBy\OperatorNegationable
 */
class Not extends BaseOperator {
    public function getName(): string {
        return 'not';
    }

    protected function getDescription(): string {
        return 'Not.';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, $map[SearchByDirective::TypeFlag], true);
    }
}
