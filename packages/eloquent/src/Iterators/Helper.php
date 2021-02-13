<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

use const PHP_INT_MAX;

/**
 * @internal
 */
trait Helper {
    protected function getLimit(QueryBuilder|EloquentBuilder $query): int {
        $query = $this->getQueryBuilder($query);
        $limit = PHP_INT_MAX;

        if ($query->unions) {
            $limit = $query->unionLimit ?? $limit;
        } else {
            $limit = $query->limit ?? $limit;
        }

        return $limit;
    }

    protected function hasUnions(QueryBuilder|EloquentBuilder $query): bool {
        return (bool) $this->getQueryBuilder($query)->unions;
    }

    protected function getQueryBuilder(QueryBuilder|EloquentBuilder $query): QueryBuilder {
        if ($query instanceof EloquentBuilder) {
            $query = $query->toBase();
        }

        return $query;
    }
}
