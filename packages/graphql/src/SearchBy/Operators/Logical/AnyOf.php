<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\LogicalOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;

/**
 * @internal Must not be used directly.
 */
class AnyOf extends BaseOperator implements LogicalOperator {
    public static function getName(): string {
        return 'anyOf';
    }

    public function getFieldDescription(): string {
        return 'Any of the conditions must be true.';
    }

    public function getFieldType(TypeProvider $provider, string $type): string {
        return "[{$type}!]";
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
        foreach ($conditions as $condition) {
            $builder = $builder->where(
                static function (EloquentBuilder|QueryBuilder $builder) use (
                    $search,
                    $condition,
                    $tableAlias,
                ): EloquentBuilder|QueryBuilder {
                    return $search->process($builder, $condition, $tableAlias);
                },
                null,
                null,
                'or',
            );
        }

        return $builder;
    }
}
