<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorNegationable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;

class Like extends BaseOperator implements ComparisonOperator, OperatorNegationable {
    public function getName(): string {
        return 'like';
    }

    protected function getDescription(): string {
        return 'Like.';
    }

    public function apply(
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        mixed $value,
        bool $not,
    ): EloquentBuilder|QueryBuilder {
        return $not
            ? $builder->where($property, 'not like', $value)
            : $builder->where($property, 'like', $value);
    }
}
