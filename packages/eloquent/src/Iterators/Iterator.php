<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use Countable;
use Illuminate\Support\Collection;
use IteratorAggregate;

/**
 * @template TItem
 *
 * @extends IteratorAggregate<int,TItem>
 */
interface Iterator extends IteratorAggregate, Countable {
    public function getIndex(): int;

    public function setIndex(int $index): static;

    public function getLimit(): ?int;

    public function setLimit(?int $limit): static;

    public function getChunkSize(): int;

    public function setChunkSize(int $chunk): static;

    public function getOffset(): string|int|null;

    public function setOffset(string|int|null $offset): static;

    /**
     * Adds the closure that will be called after received each non-empty chunk.
     *
     * @param Closure(Collection<array-key,TItem>): void|null $closure `null` removes all observers
     */
    public function onBeforeChunk(?Closure $closure): static;

    /**
     * Adds the closure that will be called after non-empty chunk processed.
     *
     * @param Closure(Collection<int,TItem>): void|null $closure `null` removes all observers
     */
    public function onAfterChunk(?Closure $closure): static;
}
