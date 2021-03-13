<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
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
 * @see \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator
 */
class ChunkedIterator extends Iterator {
    public function getIterator(): Traversable {
        $page  = 0;
        $index = 0;
        $limit = $this->getLimit($this->builder);

        do {
            $page  = $page + 1;
            $items = $this->builder->forPage($page, $this->chunk)->get();
            $count = $items->count();

            if ($this->each) {
                ($this->each)($items);
            }

            foreach ($items as $item) {
                yield $index++ => $item;

                if ($index >= $limit) {
                    break 2;
                }
            }
        } while ($count >= $this->chunk);
    }

    /**
     * Returns change safe iterator.
     */
    public function safe(string $column = null): ChunkedChangeSafeIterator {
        return (new ChunkedChangeSafeIterator($this->chunk, $this->builder, $column))->each($this->each);
    }
}
