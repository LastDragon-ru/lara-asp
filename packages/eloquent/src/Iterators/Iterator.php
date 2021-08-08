<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use IteratorAggregate;

/**
 * @template T
 */
interface Iterator extends IteratorAggregate {
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
     * Sets the closure that will be called after received each non-empty chunk.
     *
     * @return $this<T>
     */
    public function onBeforeChunk(?Closure $closure): static;

    /**
     * Sets the closure that will be called after non-empty chunk processed.
     *
     * @return $this<T>
     */
    public function onAfterChunk(?Closure $closure): static;
}
