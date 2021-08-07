<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Closure;
use Illuminate\Database\Query\Builder;
use LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator;
use LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator;

class QueryBuilderMixin {
    public function getDefaultKeyName(): Closure {
        /**
         * @internal
         */
        return function (): string {
            /**
             * @phpstan-ignore-next-line https://github.com/phpstan/phpstan/issues/4488
             * @var Builder $this
             */
            return $this->defaultKeyName();
        };
    }

    public function iterator(): Closure {
        return function (int $chunk = null): ChunkedIterator {
            /** @var Builder $this */
            $iterator = new ChunkedIterator($this);

            if ($chunk) {
                $iterator->setChunkSize($chunk);
            }

            return $iterator;
        };
    }

    public function changeSafeIterator(): Closure {
        return function (int $chunk = null, string $column = null): ChunkedChangeSafeIterator {
            /** @var Builder $this */
            $iterator = new ChunkedChangeSafeIterator($this, $column);

            if ($chunk) {
                $iterator->setChunkSize($chunk);
            }

            return $iterator;
        };
    }
}
