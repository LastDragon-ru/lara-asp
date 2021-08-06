<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Closure;
use LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator;
use LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator;
use Traversable;

class QueryBuilderMixin {
    public function getDefaultKeyName(): Closure {
        /**
         * @internal
         */
        return function (): string {
            /** @var \Illuminate\Database\Query\Builder $this */
            return $this->defaultKeyName();
        };
    }

    public function iterator(): Closure {
        return function (int $chunk = null): Traversable {
            /** @var \Illuminate\Database\Query\Builder $this */
            $iterator = new ChunkedIterator($this);

            if ($chunk) {
                $iterator->setChunkSize($chunk);
            }

            return $iterator;
        };
    }

    public function changeSafeIterator(): Closure {
        return function (int $chunk = null, string $column = null): Traversable {
            /** @var \Illuminate\Database\Query\Builder $this */
            $iterator = new ChunkedChangeSafeIterator($this, $column);

            if ($chunk) {
                $iterator->setChunkSize($chunk);
            }

            return $iterator;
        };
    }
}
