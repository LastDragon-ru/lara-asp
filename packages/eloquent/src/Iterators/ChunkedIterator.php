<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use IteratorAggregate;
use Traversable;

/**
 * The iterator that grabs rows by chunk.
 *
 * Similar to {@link \Illuminate\Database\Query\Builder::chunk()} but uses
 * generators instead of {@link \Closure}. Be careful, you should not modify/delete
 * the items while iteration or you will get unexpected results (eg missing
 * items). If you need to modify/delete items while iteration you can use
 * {@link \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator::safe()}
 * that will return the change safe iterator.
 *
 * @see \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator::safe()
 * @see \LastDragon_ru\LaraASP\Eloquent\Iterators\ChangeSafeChunkedIterator
 */
class ChunkedIterator implements IteratorAggregate {
    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private     $builder;
    private int $chunk;

    /**
     * ChunkedIterator constructor.
     *
     * @param int                                                                      $chunk
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $builder
     */
    public function __construct(int $chunk, $builder) {
        $this->chunk   = $chunk;
        $this->builder = clone $builder;
    }

    public function getIterator(): Traversable {
        $page = 0;

        do {
            $page  = $page + 1;
            $items = $this->builder->forPage($page, $this->chunk)->get();
            $count = $items->count();

            foreach ($items as $item) {
                yield $item;
            }
        } while ($count >= $this->chunk);
    }

    /**
     * Returns change safe iterator.
     */
    public function safe(string $column = null): ChangeSafeChunkedIterator {
        return new ChangeSafeChunkedIterator($this->chunk, $this->builder, $column);
    }
}
