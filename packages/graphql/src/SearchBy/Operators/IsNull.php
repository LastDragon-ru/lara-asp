<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\OperatorNegationable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchByDirective;

/**
 * @internal Must not be used directly.
 */
class IsNull extends BaseOperator implements OperatorNegationable {
    public function getName(): string {
        return 'isNull';
    }

    protected function getDescription(): string {
        return 'IS NULL?';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, $map[SearchByDirective::TypeFlag], true);
    }
}
