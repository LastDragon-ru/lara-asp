<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\PhpUnit\Requirements;

use Stringable;

interface Requirement extends Stringable {
    public function isSatisfied(): bool;
}
