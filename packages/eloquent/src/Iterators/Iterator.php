<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use Illuminate\Support\Collection;
use IteratorAggregate;

/**
 * @template T
 *
 * @extends IteratorAggregate<T>
 */
interface Iterator extends IteratorAggregate {
    public function getIndex(): int;

    /**
     * @return $this<T>
     */
    public function setIndex(int $index): static;

    public function getLimit(): ?int;

    /**
     * @return $this<T>
     */
    public function setLimit(?int $limit): static;

    public function getChunkSize(): int;

    /**
     * @return $this<T>
     */
    public function setChunkSize(int $chunk): static;

    public function getOffset(): string|int|null;

    /**
     * @return $this<T>
     */
    public function setOffset(string|int|null $offset): static;

    /**
     * Adds the closure that will be called after received each non-empty chunk.
     *
     * @param Closure(Collection<T>): void|null $closure `null` removes all observers
     *
     * @return $this<T>
     */
    public function onBeforeChunk(?Closure $closure): static;

    /**
     * Adds the closure that will be called after non-empty chunk processed.
     *
     * @param Closure(Collection<T>): void|null $closure `null` removes all observers
     *
     * @return $this<T>
     */
    public function onAfterChunk(?Closure $closure): static;
}
