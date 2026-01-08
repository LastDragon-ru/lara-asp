<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

/**
 * If two nodes have the same class and implement this interface,
 * the {@see NodeParentFactory} will merge them together.
 */
interface NodeMergeable {
    /**
     * @param ($this) $previous
     * @param ($this) $current
     *
     * @return $this
     */
    public static function merge(self $previous, self $current): self;
}
