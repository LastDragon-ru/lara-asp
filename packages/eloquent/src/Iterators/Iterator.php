<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use Illuminate\Support\Collection;
use IteratorAggregate;

/**
 * @template TItem
 *
 * @extends IteratorAggregate<int,TItem>
 */
interface Iterator extends IteratorAggregate {
    public function getIndex(): int;

    /**
     * @return $this<TItem>
     */
    public function setIndex(int $index): static;

    public function getLimit(): ?int;

    /**
     * @return $this<TItem>
     */
    public function setLimit(?int $limit): static;

    public function getChunkSize(): int;

    /**
     * @return $this<TItem>
     */
    public function setChunkSize(int $chunk): static;

    public function getOffset(): string|int|null;

    /**
     * @return $this<TItem>
     */
    public function setOffset(string|int|null $offset): static;

    /**
     * Adds the closure that will be called after received each non-empty chunk.
     *
     * @param Closure(Collection<array-key,TItem>): void|null $closure `null` removes all observers
     *
     * @return $this<TItem>
     */
    public function onBeforeChunk(?Closure $closure): static;

    /**
     * Adds the closure that will be called after non-empty chunk processed.
     *
     * @param Closure(Collection<int,TItem>): void|null $closure `null` removes all observers
     *
     * @return $this<TItem>
     */
    public function onAfterChunk(?Closure $closure): static;
}
