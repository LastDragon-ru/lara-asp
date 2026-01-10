<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

interface DataProvider {
    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public function getData(bool $raw = false): array;
}
