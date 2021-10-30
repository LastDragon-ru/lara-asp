<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use IteratorAggregate;

use function count;

/**
 * The iterator that grabs rows by chunk.
 *
 * Similar to {@link \Illuminate\Database\Query\Builder::chunk()} but uses
 * generators instead of {@link \Closure}. Be careful, you should not modify/delete
 * the items while iteration or you will get unexpected results (eg missing
 * items). If you need to modify/delete items while iteration you can use
 * {@link \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator}.
 *
 * @see \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator
 *
 * @template T
 *
 * @extends IteratorImpl<T>
 */
class ChunkedIterator extends IteratorImpl {
    protected function getChunk(EloquentBuilder|QueryBuilder $builder, int $chunk): Collection {
        return $builder->offset($this->getOffset())->limit($chunk)->get();
    }

    protected function chunkProcessed(Collection $items): bool {
        $this->setOffset($this->getOffset() + count($items));

        return parent::chunkProcessed($items);
    }

    public function getOffset(): int {
        return (int) parent::getOffset();
    }
}
