<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Ast;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use LogicException;
use Override;
use Traversable;

use function assert;
use function is_object;

/**
 * @see ParentNode
 *
 * @template TNode of object
 *
 * @property-read ?$this $previous
 * @property-read ?$this $next
 *
 * @implements IteratorAggregate<int, self<(TNode is ParentNode<covariant object> ? template-type<TNode, ParentNode, 'TChild'> : object)>>
 * @implements ArrayAccess<int<0, max>, self<(TNode is ParentNode<covariant object> ? template-type<TNode, ParentNode, 'TChild'> : object)>>
 */
readonly class Cursor implements IteratorAggregate, ArrayAccess, Countable {
    final public function __construct(
        /**
         * @var TNode
         */
        public object $node,
        /**
         * @var self<ParentNode<TNode>>|null
         */
        public ?self $parent = null,
        public ?int $index = null,
    ) {
        // empty
    }

    public function __isset(string $name): bool {
        return $this->__get($name) !== null;
    }

    public function __get(string $name): mixed {
        return match ($name) {
            'previous' => $this->index !== null ? ($this->parent[$this->index - 1] ?? null) : null,
            'next'     => $this->index !== null ? ($this->parent[$this->index + 1] ?? null) : null,
            default    => null,
        };
    }

    #[Override]
    public function count(): int {
        return $this->node instanceof ParentNode
            ? $this->node->count()
            : 0;
    }

    #[Override]
    public function getIterator(): Traversable {
        if ($this->node instanceof ParentNode) {
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
        return $this->node instanceof ParentNode
            && $this->node->offsetExists($offset);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        $child = $this->node instanceof ParentNode
            ? $this->node->offsetGet($offset)
            : null;
        $child = $child !== null && is_object($child)
            ? new static($child, $this, $offset)
            : null;

        return $child;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        throw new LogicException('Not supported.');
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        throw new LogicException('Not supported.');
    }
}
