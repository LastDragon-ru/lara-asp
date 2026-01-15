<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\Requirements\Contracts;

use Stringable;

interface Requirement extends Stringable {
    public function isSatisfied(): bool;
}
