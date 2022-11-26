<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use Laravel\Scout\Builder;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\Assert;

/**
 * @mixin Assert
 */
trait ScoutAssertions {
    /**
     * Asserts that Scout Query equals Scout Query.
     *
     * @param Builder|array<string, mixed>|string $expected
     * @param Builder|array<string, mixed>|string $actual
     */
    public static function assertScoutQueryEquals(
        Builder|array|string $expected,
        Builder|array|string $actual,
        string $message = '',
    ): void {
        static::assertEquals(Args::getScoutQuery($expected), Args::getScoutQuery($actual), $message);
    }
}
