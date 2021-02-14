<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

interface DataProvider {
    /**
     * @return array<mixed>
     */
    public function getData(): array;
}
