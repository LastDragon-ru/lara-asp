<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use Stringable;

interface NodeInfo extends Stringable {
    public function getType(): string;

    public function isNullable(): ?bool;

    public function isList(): ?bool;

    public function __toString(): string;
}
