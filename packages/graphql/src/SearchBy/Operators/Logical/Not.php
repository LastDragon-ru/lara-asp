<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\LogicalOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;

class Not extends Logical implements LogicalOperator {
    public static function getName(): string {
        return 'not';
    }

    public function getFieldDescription(): string {
        return 'Not.';
    }

    /**
     * @inheritDoc
     */
    public function apply(
        SearchBuilder $search,
        EloquentBuilder|QueryBuilder $builder,
        array $conditions,
        ?string $tableAlias,
    ): EloquentBuilder|QueryBuilder {
        return $builder->where(
            static function (EloquentBuilder|QueryBuilder $builder) use (
                $search,
                $conditions,
                $tableAlias,
            ): EloquentBuilder|QueryBuilder {
                return $search->process($builder, $conditions, $tableAlias);
            },
            null,
            null,
            'and not',
        );
    }

    protected function getBoolean(): string {
        return 'and not';
    }
}
