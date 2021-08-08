<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

trait WithTestData {
    /**
     * @param class-string|null $class
     */
    public function getTestData(string $class = null): TestData {
        return new TestData($class ?? static::class);
    }
}
