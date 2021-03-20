<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorNegationable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;

/**
 * @internal Must not be used directly.
 */
abstract class Logical extends BaseOperator implements LogicalOperator, OperatorNegationable {
    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, "[{$scalar}!]", true);
    }
}
