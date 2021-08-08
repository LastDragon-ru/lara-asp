<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use EmptyIterator;
use Generator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

use function min;

/**
 * @internal
 */
abstract class IteratorImpl implements Iterator {
    protected ?Closure        $beforeChunk = null;
    protected ?Closure        $afterChunk  = null;
    protected ?int            $limit       = null;
    protected int             $chunk       = 1000;
    protected string|int|null $offset      = null;

    public function __construct(
        protected QueryBuilder|EloquentBuilder $builder,
    ) {
        $this->setLimit($this->getDefaultLimit());
        $this->setOffset($this->getDefaultOffset());
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

    /**
     * Sets the closure that will be called after received each chunk.
     */
    public function onBeforeChunk(?Closure $closure): static {
        $this->beforeChunk = $closure;

        return $this;
    }

    /**
     * Sets the closure that will be called after chunk processed.
     */
    public function onAfterChunk(?Closure $closure): static {
        $this->afterChunk = $closure;

        return $this;
    }

    /**
     * @return Generator<Model|array<string,mixed>>
     */
    public function getIterator(): Generator {
        // Prepare
        $index = 0;
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
            }

            if (!$this->chunkProcessed($items) || ($limit && $index >= $limit)) {
                break;
            }
        } while (!$items->isEmpty());
    }

    /**
     * @return Collection<Model|array<string,mixed>>
     */
    abstract protected function getChunk(QueryBuilder|EloquentBuilder $builder, int $chunk): Collection;

    /**
     * @param Collection<Model|array<string,mixed>> $items
     */
    protected function chunkLoaded(Collection $items): void {
        if ($this->beforeChunk && !$items->isEmpty()) {
            ($this->beforeChunk)($items);
        }
    }

    /**
     * @param Collection<Model|array<string,mixed>> $items
     */
    protected function chunkProcessed(Collection $items): bool {
        if ($this->afterChunk && !$items->isEmpty()) {
            ($this->afterChunk)($items);
        }

        return true;
    }

    protected function getDefaultLimit(): ?int {
        $builder = $this->getQueryBuilder();
        $limit   = null;

        if ($builder->unions) {
            $limit = $builder->unionLimit ?? $limit;
        } else {
            $limit = $builder->limit ?? $limit;
        }

        return $limit;
    }

    protected function getDefaultOffset(): ?int {
        $builder = $this->getQueryBuilder();
        $limit   = null;

        if ($builder->unions) {
            $limit = $builder->unionOffset ?? $limit;
        } else {
            $limit = $builder->offset ?? $limit;
        }

        return $limit;
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
