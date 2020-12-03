<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

trait WithTestData {
    public function getTestData(string $class = null): TestData {
        return new TestData($class ?? static::class);
    }
}
