<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

/**
 * Allows to get instance of {@see TestData} (a small helper to load data
 * associated with test)
 *
 * @deprecated %{VERSION} The `\LastDragon_ru\PhpUnit\Utils\TestData` should be used instead.
 */
trait WithTestData {
    /**
     * @param class-string|null $class
     */
    public static function getTestData(?string $class = null): TestData {
        return new TestData($class ?? static::class);
    }
}
