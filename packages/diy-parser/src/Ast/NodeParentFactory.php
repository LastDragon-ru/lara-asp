<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Ast;

use function array_key_last;
use function end;

/**
 * @see NodeMergeable
 *
 * @template TParent of NodeParent<covariant TChild>
 * @template TChild of NodeChild
 */
abstract class NodeParentFactory {
    /**
     * @var list<covariant TChild>
     */
    private array $children = [];

    public function __construct() {
        // empty
    }

    public function isEmpty(): bool {
        return $this->children === [];
    }

    /**
     * @return ?TParent
     */
    public function create(): ?object {
        $node           = $this->onCreate($this->children);
        $this->children = [];

        return $node;
    }

    /**
     * @param list<TChild> $children
     *
     * @return ?TParent
     */
    abstract protected function onCreate(array $children): ?object;

    /**
     * @param ?TChild $node
     */
    public function push(?object $node): bool {
        // Null? Skip
        if ($node === null) {
            return false;
        }

        // Same?
        $key      = null;
        $previous = end($this->children);
        $previous = $previous !== false ? $previous : null;

        if ($previous instanceof NodeMergeable && $previous instanceof $node && $node instanceof $previous) {
            $key  = array_key_last($this->children);
            $node = $node::merge($previous, $node);
        }

        // Allowed?
        if (!$this->onPush($this->children, $node)) {
            return false;
        }

        // Push
        if ($key !== null && isset($this->children[$key])) {
            $this->children[$key] = $node;
        } else {
            $this->children[] = $node;
        }

        // Return
        return true;
    }

    /**
     * @param list<TChild> $children
     * @param ?TChild      $node
     *
     */
    abstract protected function onPush(array $children, ?object $node): bool;
}
