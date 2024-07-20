<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator;
use LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator;

/**
 * Eloquent builder mixin.
 */
class EloquentBuilderMixin {
    /**
     * @return Closure(string=): Builder<Model>
     */
    public function orderByKey(): Closure {
        return function (string $direction = 'asc'): Builder {
            /** @var Builder<Model> $this */
            return $this->orderBy($this->qualifyColumn($this->getModel()->getKeyName()), $direction);
        };
    }

    /**
     * @return Closure(): Builder<Model>
     */
    public function orderByKeyDesc(): Closure {
        return function (): Builder {
            /** @var Builder<Model> $this */
            return $this->orderByKey('desc');
        };
    }

    /**
     * @return Closure(int=): ChunkedIterator<Model>
     */
    public function getChunkedIterator(): Closure {
        return function (?int $chunk = null): ChunkedIterator {
            /** @var Builder<Model> $this */
            $iterator = new ChunkedIterator($this);

            if ($chunk) {
                $iterator->setChunkSize($chunk);
            }

            return $iterator;
        };
    }

    /**
     * @return Closure(int=,string=): ChunkedChangeSafeIterator<Model>
     */
    public function getChangeSafeIterator(): Closure {
        return function (?int $chunk = null, ?string $column = null): ChunkedChangeSafeIterator {
            /** @var Builder<Model> $this */
            $iterator = new ChunkedChangeSafeIterator($this, $column);

            if ($chunk) {
                $iterator->setChunkSize($chunk);
            }

            return $iterator;
        };
    }
}
