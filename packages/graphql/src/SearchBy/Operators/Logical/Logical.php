<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorNegationable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;

/**
 * @internal Must not be used directly.
 */
abstract class Logical extends BaseOperator implements LogicalOperator, OperatorNegationable {
    protected function getDescription(): string {
        return "Logical `{$this->getName()}`.";
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, "[{$scalar}!]", true);
    }

    public function apply(EloquentBuilder|QueryBuilder $builder, Closure $nested): EloquentBuilder|QueryBuilder {
        return $builder->where($nested, null, null, $this->getName());
    }
}
