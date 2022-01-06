<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use EmptyIterator;
use Generator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;

use function min;

/**
 * @template T
 *
 * @implements Iterator<T>
 *
 * @internal
 */
abstract class IteratorImpl implements Iterator {
    protected Dispatcher      $beforeChunk;
    protected Dispatcher      $afterChunk;
    protected int             $index  = 0;
    protected ?int            $limit  = null;
    protected int             $chunk  = 1000;
    protected string|int|null $offset = null;

    public function __construct(
        protected QueryBuilder|EloquentBuilder $builder,
    ) {
        $this->beforeChunk = new Dispatcher();
        $this->afterChunk  = new Dispatcher();

        $this->setLimit($this->getDefaultLimit());
        $this->setOffset($this->getDefaultOffset());
    }

    public function getIndex(): int {
        return $this->index;
    }

    public function setIndex(int $index): static {
        $this->index = $index;

        return $this;
    }

    public function getLimit(): ?int {
        return $this->limit;
    }

    public function setLimit(?int $limit): static {
        $this->limit = $limit;

        return $this;
    }

    public function getChunkSize(): int {
        return $this->chunk;
    }

    public function setChunkSize(int $chunk): static {
        $this->chunk = $chunk;

        return $this;
    }

    public function getOffset(): string|int|null {
        return $this->offset;
    }

    public function setOffset(string|int|null $offset): static {
        $this->offset = $offset;

        return $this;
    }

    public function onBeforeChunk(?Closure $closure): static {
        if ($closure) {
            $this->beforeChunk->attach($closure);
        } else {
            $this->beforeChunk->reset();
        }

        return $this;
    }

    public function onAfterChunk(?Closure $closure): static {
        if ($closure) {
            $this->afterChunk->attach($closure);
        } else {
            $this->afterChunk->reset();
        }

        return $this;
    }

    /**
     * @return Generator<T>
     */
    public function getIterator(): Generator {
        // Prepare
        $index = $this->getIndex();
        $chunk = $this->limit ? min($this->limit, $this->chunk) : $this->chunk;
        $limit = $this->limit;

        // Limit?
        if ($limit <= 0 && $limit !== null) {
            return new EmptyIterator();
        }

        // Iterate
        do {
            $chunk = $limit ? min($chunk, $limit - $index) : $chunk;
            $items = $this->getChunk((clone $this->getBuilder())->offset(0), $chunk);

            $this->chunkLoaded($items);

            foreach ($items as $item) {
                yield $index++ => $item;

                $this->setIndex($index);
            }

            if (!$this->chunkProcessed($items) || ($limit && $index >= $limit)) {
                break;
            }
        } while (!$items->isEmpty());
    }

    /**
     * @return Collection<T>
     */
    abstract protected function getChunk(QueryBuilder|EloquentBuilder $builder, int $chunk): Collection;

    /**
     * @param Collection<T> $items
     */
    protected function chunkLoaded(Collection $items): void {
        if (!$items->isEmpty()) {
            $this->beforeChunk->notify($items);
        }
    }

    /**
     * @param Collection<T> $items
     */
    protected function chunkProcessed(Collection $items): bool {
        if (!$items->isEmpty()) {
            $this->afterChunk->notify($items);
        }

        return true;
    }

    protected function getDefaultLimit(): ?int {
        $builder = $this->getQueryBuilder();
        $limit   = $builder->unions
            ? $builder->unionLimit
            : $builder->limit;

        return $limit;
    }

    protected function getDefaultOffset(): ?int {
        $builder = $this->getQueryBuilder();
        $offset  = $builder->unions
            ? $builder->unionOffset
            : $builder->offset;

        return $offset;
    }

    protected function getBuilder(): EloquentBuilder|QueryBuilder {
        return $this->builder;
    }

    protected function getQueryBuilder(): QueryBuilder {
        $builder = $this->getBuilder();

        if ($builder instanceof EloquentBuilder) {
            $builder = $builder->toBase();
        }

        return $builder;
    }
}
