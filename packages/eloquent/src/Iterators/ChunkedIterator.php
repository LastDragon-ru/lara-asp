<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Override;

use function count;

/**
 * The iterator that grabs rows by chunk.
 *
 * Similar to {@link \Illuminate\Database\Query\Builder::chunk()} but uses
 * generators instead of {@link \Closure}. Be careful, you should not modify/delete
 * the items while iteration or you will get unexpected results (eg missing
 * items). If you need to modify/delete items while iteration you can use
 * {@link ChunkedChangeSafeIterator}.
 *
 * @see      ChunkedChangeSafeIterator
 *
 * @template TItem of Model
 *
 * @extends IteratorImpl<TItem>
 */
class ChunkedIterator extends IteratorImpl {
    #[Override]
    protected function getChunk(Builder $builder, int $chunk): Collection {
        $builder
            ->offset($this->getOffset())
            ->limit($chunk);

        return $builder->get();
    }

    #[Override]
    protected function chunkProcessed(Collection $items): bool {
        $this->setOffset($this->getOffset() + count($items));

        return parent::chunkProcessed($items);
    }

    #[Override]
    public function getOffset(): int {
        return (int) parent::getOffset();
    }
}
