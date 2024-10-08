<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use EmptyIterator;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use Override;

use function count;
use function max;
use function min;

/**
 * @template TItem of Model
 *
 * @implements Iterator<TItem>
 *
 * @internal
 */
abstract class IteratorImpl implements Iterator {
    /**
     * @var Dispatcher<Collection<string|int,TItem>>
     */
    protected Dispatcher $beforeChunk;
    /**
     * @var Dispatcher<Collection<string|int,TItem>>
     */
    protected Dispatcher $afterChunk;

    protected int             $index  = 0;
    protected ?int            $limit  = null;
    protected int             $chunk  = 1000;
    protected string|int|null $offset = null;

    /**
     * @param Builder<TItem> $builder
     */
    public function __construct(
        protected Builder $builder,
    ) {
        $this->beforeChunk = new Dispatcher();
        $this->afterChunk  = new Dispatcher();

        $this->setLimit($this->getDefaultLimit());
        $this->setOffset($this->getDefaultOffset());
    }

    #[Override]
    public function getIndex(): int {
        return $this->index;
    }

    #[Override]
    public function setIndex(int $index): static {
        $this->index = $index;

        return $this;
    }

    #[Override]
    public function getLimit(): ?int {
        return $this->limit;
    }

    #[Override]
    public function setLimit(?int $limit): static {
        $this->limit = $limit;

        return $this;
    }

    #[Override]
    public function getChunkSize(): int {
        return $this->chunk;
    }

    #[Override]
    public function setChunkSize(int $chunk): static {
        $this->chunk = $chunk;

        return $this;
    }

    #[Override]
    public function getOffset(): string|int|null {
        return $this->offset;
    }

    #[Override]
    public function setOffset(string|int|null $offset): static {
        $this->offset = $offset;

        return $this;
    }

    #[Override]
    public function onBeforeChunk(?Closure $closure): static {
        if ($closure !== null) {
            $this->beforeChunk->attach($closure);
        } else {
            $this->beforeChunk->reset();
        }

        return $this;
    }

    #[Override]
    public function onAfterChunk(?Closure $closure): static {
        if ($closure !== null) {
            $this->afterChunk->attach($closure);
        } else {
            $this->afterChunk->reset();
        }

        return $this;
    }

    /**
     * @return Generator<int,TItem>
     */
    #[Override]
    public function getIterator(): Generator {
        // Prepare
        $index = $this->getIndex();
        $chunk = $this->limit > 0 ? min($this->limit, $this->chunk) : $this->chunk;
        $limit = $this->limit;

        // Limit?
        if ($limit !== null && $limit <= 0) {
            return new EmptyIterator();
        }

        // Iterate
        do {
            $builder = (clone $this->getBuilder())->tap(static function (Builder $builder): void {
                $builder->offset(0);
            });
            $chunk   = $limit > 0 ? min($chunk, $limit - $index) : $chunk;
            $items   = $this->getChunk($builder, $chunk);
            $count   = count($items);

            $this->chunkLoaded($items);

            foreach ($items as $item) {
                yield $index++ => $item;

                $this->setIndex($index);
            }

            if (!$this->chunkProcessed($items) || ($limit > 0 && $index >= $limit)) {
                break;
            }
        } while ($count !== 0 && $count >= $chunk);
    }

    /**
     * @param Builder<TItem> $builder
     *
     * @return Collection<array-key,TItem>
     */
    abstract protected function getChunk(Builder $builder, int $chunk): Collection;

    /**
     * @param Collection<array-key,TItem> $items
     */
    protected function chunkLoaded(Collection $items): void {
        if (!$items->isEmpty()) {
            $this->beforeChunk->notify($items);
        }
    }

    /**
     * @param Collection<array-key,TItem> $items
     */
    protected function chunkProcessed(Collection $items): bool {
        if (!$items->isEmpty()) {
            $this->afterChunk->notify($items);
        }

        return true;
    }

    protected function getDefaultLimit(): ?int {
        $builder = $this->getBuilder()->getQuery();
        $limit   = $builder->unions !== null && $builder->unions !== []
            ? $builder->unionLimit
            : $builder->limit;

        return $limit;
    }

    protected function getDefaultOffset(): ?int {
        $builder = $this->getBuilder()->getQuery();
        $offset  = $builder->unions !== null && $builder->unions !== []
            ? $builder->unionOffset
            : $builder->offset;

        return $offset;
    }

    /**
     * @return Builder<TItem>
     */
    protected function getBuilder(): Builder {
        return $this->builder;
    }

    #[Override]
    public function count(): int {
        $limit = $this->getLimit();
        $count = $this->getBuilder()->getQuery()->count();
        $count = $limit !== null ? min($limit, $count) : $count;
        $count = max(0, $count);

        return $count;
    }
}
