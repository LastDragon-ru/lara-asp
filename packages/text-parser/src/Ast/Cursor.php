<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use LastDragon_ru\TextParser\Exceptions\OffsetReadonly;
use Override;
use Traversable;

use function assert;
use function is_object;

/**
 * @see NodeParent
 * @see NodeChild
 *
 * @template TNode of object
 *
 * @property-read ?$this $previous
 * @property-read ?$this $next
 *
 * @implements IteratorAggregate<int, (TNode is NodeParent<covariant object> ? self<template-type<TNode, NodeParent, 'TChild'>> : null)>
 * @implements ArrayAccess<int<0, max>, (TNode is NodeParent<covariant object> ? self<template-type<TNode, NodeParent, 'TChild'>> : null)>
 */
readonly class Cursor implements IteratorAggregate, ArrayAccess, Countable {
    final public function __construct(
        /**
         * @var TNode
         */
        public object $node,
        /**
         * @var (TNode is NodeChild<object> ? self<template-type<TNode, NodeChild, 'TParent'>>|null : null)
         */
        public ?self $parent = null,
        public ?int $index = null,
    ) {
        // empty
    }

    /**
     * @deprecated 10.0.0 Will be replaced to property hooks soon.
     */
    public function __isset(string $name): bool {
        return $this->__get($name) !== null;
    }

    /**
     * @deprecated 10.0.0 Will be replaced to property hooks soon.
     */
    public function __get(string $name): mixed {
        return match ($name) {
            'previous' => $this->index !== null ? ($this->parent[$this->index - 1] ?? null) : null,
            'next'     => $this->index !== null ? ($this->parent[$this->index + 1] ?? null) : null,
            default    => null,
        };
    }

    #[Override]
    public function count(): int {
        return $this->node instanceof NodeParent
            ? $this->node->count()
            : 0;
    }

    #[Override]
    public function getIterator(): Traversable {
        if ($this->node instanceof NodeParent) {
            foreach ($this->node as $key => $child) {
                assert(is_object($child), 'https://github.com/phpstan/phpstan/issues/13204');

                yield $key => new static($child, $this, $key);
            }
        } else {
            yield from [];
        }
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return $this->node instanceof NodeParent
            && $this->node->offsetExists($offset);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        $child = $this->node instanceof NodeParent
            ? $this->node->offsetGet($offset)
            : null;
        $child = $child !== null && is_object($child)
            ? new static($child, $this, $offset)
            : null;

        return $child;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        throw new OffsetReadonly($offset);
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        throw new OffsetReadonly($offset);
    }
}
