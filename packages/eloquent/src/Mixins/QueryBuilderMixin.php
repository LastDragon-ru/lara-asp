<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Closure;
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
        return function (int $chunk = 100): Traversable {
            /** @var \Illuminate\Database\Query\Builder $this */
            return new ChunkedIterator($chunk, $this);
        };
    }
}
