<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\OperatorNegationable;

/**
 * @internal Must not be used directly.
 */
class Has extends BaseOperator implements OperatorNegationable {
    public function getName(): string {
        return 'has';
    }

    public function getPrecedence(): int {
        return static::PrecedenceStructural;
    }

    protected function getDescription(): string {
        return 'Has?';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, "[{$scalar}!]!", true);
    }
}
