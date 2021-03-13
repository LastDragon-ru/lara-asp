<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use InvalidArgumentException;
use stdClass;
use Traversable;

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
 * To create an instance you can use:
 *
 *      // $query is \Illuminate\Database\Query\Builder
 *      //        or \Illuminate\Database\Eloquent\Builder
 *      $query->iterator()->safe()
 *
 * @see \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator::safe()
 * @see https://github.com/laravel/framework/issues/35400
 */
class ChunkedChangeSafeIterator extends Iterator {
    private string $column;

    public function __construct(int $chunk, QueryBuilder|EloquentBuilder $builder, string $column = null) {
        parent::__construct($chunk, $builder);

        $this->column = $column ?? $builder->getDefaultKeyName();

        // Unfortunately the `forPageAfterId()` doesn't correctly work with UNION,
        // it just adds conditional to the main query, and this leads to an
        // infinite loop.
        if ($this->hasUnions($builder)) {
            throw new InvalidArgumentException('Queries with UNION is not supported.');
        }
    }

    public function getIterator(): Traversable {
        $last  = null;
        $index = 0;
        $limit = $this->getLimit($this->builder);

        do {
            $items = (clone $this->builder)
                ->reorder()
                ->forPageAfterId($this->chunk, $last, $this->column)
                ->get();
            $count = $items->count();
            $last  = $this->column($items->last());

            if ($this->each) {
                ($this->each)($items);
            }

            foreach ($items as $item) {
                yield $index++ => $item;

                if ($index >= $limit) {
                    break 2;
                }
            }

            // The '0' here to select rows that may be created while iteration
        } while ($count > 0);
    }

    protected function column(Model|stdClass|array|null $item): mixed {
        $value = null;

        if (is_object($item)) {
            $value = $item->{$this->column};
        } elseif (is_array($item)) {
            $value = $item[$this->column];
        } else {
            // empty
        }

        return $value;
    }
}
