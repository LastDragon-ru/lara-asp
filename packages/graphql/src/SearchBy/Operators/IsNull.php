<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorNegationable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\ComparisonOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchByDirective;

/**
 * @internal Must not be used directly.
 */
class IsNull extends BaseOperator implements ComparisonOperator, OperatorNegationable {
    public function getName(): string {
        return 'isNull';
    }

    protected function getDescription(): string {
        return 'Is NULL?';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, $map[SearchByDirective::TypeFlag], true);
    }

    public function apply(
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        mixed $value,
        bool $not,
    ): EloquentBuilder|QueryBuilder {
        return $not
            ? $builder->whereNotNull($property)
            : $builder->whereNull($property);
    }
}
