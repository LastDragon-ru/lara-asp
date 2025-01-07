<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\Duration;

use DateInterval;
use LastDragon_ru\LaraASP\Formatter\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(DurationFormat::class)]
final class DurationFormatTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderFormat')]
    public function testFormat(string $expected, string $format, DateInterval|float|int|null $duration): void {
        $formatter = new DurationFormat([new DurationOptions($format)]);
        $actual    = $formatter($duration);

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string, DateInterval|float|int|null}>
     */
    public static function dataProviderFormat(): array {
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
            'y:M:d:H:m:s'         => ['1:2:3:1:2:5', 'y:M:d:H:m:s', new DateInterval('P1Y2M3DT1H2M5S')],
            'yyy:MM:dd:HH:mm:ss'  => ['001:02:03:01:02:05', 'yyy:MM:dd:HH:mm:ss', new DateInterval('P1Y2M3DT1H2M5S')],
            "y:M:d:'H':m:s"       => ['1:2:3:H:62:5', "y:M:d:'H':m:s", new DateInterval('P1Y2M3DT1H2M5S')],
            "y:'M':d:'H':m:s.SSS" => ['2:M:298:H:62:5.000', "y:'M':d:'H':m:s.SSS", new DateInterval('P1Y22M3DT1H2M5S')],
        ];
    }
    // </editor-fold>
}
