<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use Override;
use Traversable;

use function array_splice;
use function count;

/**
 * @template TChild of NodeChild
 *
 * @implements NodeParent<TChild>
 */
abstract class NodeParentImpl implements NodeParent {
    public function __construct(
        /**
         * @var list<TChild>
         */
        public array $children,
    ) {
        // empty
    }

    #[Override]
    public function count(): int {
        return count($this->children);
    }

    #[Override]
    public function getIterator(): Traversable {
        yield from $this->children;
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->children[$offset]);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        return $this->children[$offset] ?? null;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        array_splice($this->children, $offset ?? count($this->children), 1, $value !== null ? [$value] : []);
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        array_splice($this->children, $offset, 1);
    }
}
