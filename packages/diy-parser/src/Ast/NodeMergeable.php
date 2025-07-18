<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Ast;

interface NodeMergeable {
    /**
     * @param ($this) $previous
     * @param ($this) $current
     *
     * @return $this
     */
    public static function merge(self $previous, self $current): self;
}
