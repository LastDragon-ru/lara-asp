<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use stdClass;

use function is_array;
use function is_object;

/**
 * The iterator that grabs rows by chunk and safe for changing/deleting rows
 * while iteration.
 *
 * Similar to {@link \Illuminate\Database\Query\Builder::chunkById()} but uses
 * generators instead of {@link \Closure}. Although you can modify/delete the
 * items while iteration there are few important limitations:
 *
 * - it is not possible to sort rows, they always will be sorted by `column asc`;
 * - the `column` should not be changed while iteration or this may lead to
 *   repeating row in results;
 * - the row inserted while iteration may be skipped if it has `column` with
 *   the value that lover than the internal pointer;
 * - queries with UNION is not supported.
 *
 * @see https://github.com/laravel/framework/issues/35400
 */
class ChunkedChangeSafeIterator extends IteratorImpl {
    private string $column;

    public function __construct(QueryBuilder|EloquentBuilder $builder, string $column = null) {
        parent::__construct($builder);

        $this->column = $column ?? $builder->getDefaultKeyName();

        // Unfortunately the Laravel doesn't correctly work with UNION,
        // it just adds conditional to the main query, and this leads to an
        // infinite loop.
        if ($this->hasUnions($builder)) {
            throw new InvalidArgumentException('Queries with UNION is not supported.');
        }
    }

    public function getColumn(): string {
        return $this->column;
    }

    protected function getChunk(EloquentBuilder|QueryBuilder $builder, int $chunk): Collection {
        $column  = $this->getColumn();
        $builder = $builder->reorder()->orderBy($column, 'asc')->limit($chunk);

        if ($this->getOffset()) {
            $builder->where($column, '>', $this->getOffset());
        }

        return $builder->get();
    }

    protected function chunkProcessed(Collection $items): bool {
        $last     = $this->column($items->last());
        $continue = $last && $this->setOffset($last);

        return parent::chunkProcessed($items)
            && $continue;
    }

    protected function column(Model|stdClass|array|null $item): mixed {
        $value  = null;
        $column = $this->getColumn();

        if (is_object($item)) {
            $value = $item->{$column};
        } elseif (is_array($item)) {
            $value = $item[$column];
        } else {
            // empty
        }

        return $value;
    }

    protected function hasUnions(QueryBuilder|EloquentBuilder $query): bool {
        return (bool) $this->getQueryBuilder($query)->unions;
    }

    protected function getDefaultOffset(EloquentBuilder|QueryBuilder $builder): ?int {
        // Because Builder contains SQL offset, not column value.
        return null;
    }
}
