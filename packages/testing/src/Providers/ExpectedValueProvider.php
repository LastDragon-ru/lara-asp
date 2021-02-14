<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

interface ExpectedValueProvider {
    public function getExpectedValue(): mixed;
}
