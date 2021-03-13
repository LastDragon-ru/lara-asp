<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use IteratorAggregate;

use const PHP_INT_MAX;

/**
 * @internal
 */
abstract class Iterator implements IteratorAggregate {
    protected QueryBuilder|EloquentBuilder $builder;
    protected int                          $chunk;
    protected ?Closure                     $each = null;

    public function __construct(int $chunk, QueryBuilder|EloquentBuilder $builder) {
        $this->chunk   = $chunk;
        $this->builder = clone $builder;
    }

    /**
     * Sets the closure that will be called after received each chunk.
     */
    public function each(?Closure $each): static {
        $this->each = $each;

        return $this;
    }

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
