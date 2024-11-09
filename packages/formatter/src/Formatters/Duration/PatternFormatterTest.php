<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formatters\Duration;

use DateInterval;
use DateTime;
use LastDragon_ru\LaraASP\Formatter\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(PatternFormatter::class)]
final class PatternFormatterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderGetTimestamp')]
    public function testGetTimestamp(float $expected, DateInterval $interval): void {
        self::assertEquals($expected, PatternFormatter::getTimestamp($interval));
    }

    #[DataProvider('dataProviderFormat')]
    public function testFormat(string $expected, string $format, float|int $duration): void {
        $formatter = new PatternFormatter($format);
        $actual    = $formatter->format($duration);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{float, DateInterval}>
     */
    public static function dataProviderGetTimestamp(): array {
        return [
            'a' => [
                22 * 365 * 24 * 60 * 60 + 22 * 30 * 24 * 60 * 60 + 22 * 24 * 60 * 60 + 22 * 60 * 60 + 22 * 60 + 22,
                new DateInterval('P22Y22M22DT22H22M22S'),
            ],
            'b' => [
                -1 * (16 * 24 * 60 * 60 - 0.000484),
                (new DateTime('2023-12-27T11:22:45.000121+04:00'))->diff(
                    new DateTime('2023-12-11T11:22:45.000605+04:00'),
                ),
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, float|int}>
     */
    public static function dataProviderFormat(): array {
        $duration = static function (string $interval): float {
            return PatternFormatter::getTimestamp(new DateInterval($interval));
        };

        return [
            'S'                   => ['3', 'S', 12.345678],
            'SS'                  => ['35', 'SS', 12.345678],
            'SSS'                 => ['346', 'SSS', 12.345678],
            's.SSS'               => ['1.230', 's.SSS', 1.23],
            'ss.SS'               => ['123.45', 'ss.SS', 123.45],
            'ss.SSS'              => ['01.230', 'ss.SSS', 1.23],
            'm:ss'                => ['3:00', 'm:ss', 180],
            'mm:ss'               => ['03:00', 'mm:ss', -180],
            'zmm:ss'              => ['-03:00', 'zmm:ss', -180],
            'H:m:s'               => ['5:3:0', 'H:m:s', 5 * 60 * 60 + 180],
            'HH:mm:ss'            => ['05:03:00', 'HH:mm:ss', 5 * 60 * 60 + 180],
            'y:M:d:H:m:s'         => ['1:2:3:1:2:5', 'y:M:d:H:m:s', $duration('P1Y2M3DT1H2M5S')],
            'yyy:MM:dd:HH:mm:ss'  => ['001:02:03:01:02:05', 'yyy:MM:dd:HH:mm:ss', $duration('P1Y2M3DT1H2M5S')],
            "y:M:d:'H':m:s"       => ['1:2:3:H:62:5', "y:M:d:'H':m:s", $duration('P1Y2M3DT1H2M5S')],
            "y:'M':d:'H':m:s.SSS" => ['2:M:298:H:62:5.000', "y:'M':d:'H':m:s.SSS", $duration('P1Y22M3DT1H2M5S')],
        ];
    }
    // </editor-fold>
}
