<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Utils;

use DateInterval;
use DateTime;
use LastDragon_ru\LaraASP\Formatter\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Duration::class)]
final class DurationTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderGetTimestamp')]
    public function testGetTimestamp(float $expected, DateInterval|float|int|null $interval): void {
        self::assertSame($expected, Duration::getTimestamp($interval));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{float, DateInterval|float|int|null}>
     */
    public static function dataProviderGetTimestamp(): array {
        return [
            'DateInterval'            => [
                22 * 365 * 24 * 60 * 60 + 22 * 30 * 24 * 60 * 60 + 22 * 24 * 60 * 60 + 22 * 60 * 60 + 22 * 60 + 22,
                new DateInterval('P22Y22M22DT22H22M22S'),
            ],
            'DateInterval (negative)' => [
                -1 * (16 * 24 * 60 * 60 - 0.000484),
                (new DateTime('2023-12-27T11:22:45.000121+04:00'))->diff(
                    new DateTime('2023-12-11T11:22:45.000605+04:00'),
                ),
            ],
            'float'                   => [
                123.45,
                123.45,
            ],
            'int'                     => [
                123,
                123,
            ],
            'null'                    => [
                0,
                null,
            ],
        ];
    }
    // </editor-fold>
}
