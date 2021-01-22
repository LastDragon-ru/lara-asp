<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use const PHP_INT_MAX;

/**
 * @internal
 */
trait Helper {
    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     *
     * @return int
     */
    protected function getLimit($query): int {
        $query = $this->getQueryBuilder($query);
        $limit = PHP_INT_MAX;

        if ($query->unions) {
            $limit = $query->unionLimit ?? $limit;
        } else {
            $limit = $query->limit ?? $limit;
        }

        return $limit;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     *
     * @return bool
     */
    protected function hasUnions($query): bool {
        return (bool) $this->getQueryBuilder($query)->unions;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQueryBuilder($query): QueryBuilder {
        if ($query instanceof EloquentBuilder) {
            $query = $query->toBase();
        }

        return $query;
    }
}
