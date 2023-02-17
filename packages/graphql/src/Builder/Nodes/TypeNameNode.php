<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Nodes;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\NodeInfo;

class TypeNameNode implements NodeInfo {
    public function __construct(
        protected string $name,
        protected ?bool $nullable = null,
        protected ?bool $list = null,
    ) {
        // empty
    }

    // <editor-fold desc="NodeInfo">
    // =========================================================================
    public function getType(): string {
        return $this->name;
    }

    public function isNullable(): ?bool {
        return $this->nullable;
    }

    public function isList(): ?bool {
        return $this->list;
    }

    public function __toString(): string {
        return $this->getType();
    }
    // </editor-fold>
}
