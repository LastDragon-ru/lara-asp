<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Contracts;

use Stringable;

interface Matcher {
    public function match(Stringable|string $string): bool;
}
