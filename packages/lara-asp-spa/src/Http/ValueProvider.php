<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http;

interface ValueProvider {
    public function getValue(mixed $value): mixed;
}
