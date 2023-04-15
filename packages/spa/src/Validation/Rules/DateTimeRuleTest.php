<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

use function config;
use function is_array;

/**
 * @internal
 */
#[CoversClass(DateTimeRule::class)]
class DateTimeRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPasses
     */
    public function testPasses(bool $expected, string $value): void {
        $translator = $this->app->make(Translator::class);
        $rule       = new DateTimeRule($translator);

        self::assertEquals($expected, $rule->passes('attribute', $value));
    }

    public function testMessage(): void {
        $translator = $this->app->make(Translator::class);
        $rule       = new DateTimeRule($translator);

        self::assertEquals('The :attribute is not a valid datetime.', $rule->message());
    }

    /**
     * @dataProvider dataProviderGetValue
     *
     * @param array{class: class-string<Throwable>, message: string}|string $expected
     */
    public function testGetValue(string|array $expected, ?string $tz, string $value): void {
        if (is_array($expected)) {
            self::expectException($expected['class']);
            self::expectExceptionMessageMatches($expected['message']);
        }

        $translator = $this->app->make(Translator::class);
        $rule       = new DateTimeRule($translator);

        config([
            'app.timezone' => $tz,
        ]);

        $date = $rule->getValue($value);

        self::assertEquals($expected, $date ? $date->format('Y-m-d\TH:i:s.uP') : null);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public static function dataProviderPasses(): array {
        return [
            'valid date'                => [false, '2102-12-01'],
            'invalid date'              => [false, '02-12-01'],
            'datetime without timezone' => [false, '2102-12-01T22:12:01'],
            'datetime'                  => [true, '2102-12-01T22:12:01+00:00'],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function dataProviderGetValue(): array {
        return [
            'date'                          => [
                [
                    'class'   => InvalidArgumentException::class,
                    'message' => '/Data missing|Not enough data available to satisfy format/',
                ],
                null,
                '2102-12-01',
            ],
            'invalid date'                  => [
                [
                    'class'   => InvalidArgumentException::class,
                    'message' => '/Data missing|Not enough data available to satisfy format/',
                ],
                null,
                '02-12-01',
            ],
            'datetime without timezone'     => [
                [
                    'class'   => InvalidArgumentException::class,
                    'message' => '/Data missing|Not enough data available to satisfy format/',
                ],
                null,
                '2102-12-01T00:00:00',
            ],
            'datetime UTC + UTC'            => [
                '2102-12-01T22:12:01.000000+00:00',
                'UTC',
                '2102-12-01T22:12:01+00:00',
            ],
            'datetime Europe/Moscow + UTC'  => [
                '2102-12-02T01:12:01.000000+03:00',
                'Europe/Moscow',
                '2102-12-01T22:12:01+00:00',
            ],
            'datetime UTC + Europe/Moscow'  => [
                '2102-12-01T22:12:01.000000+00:00',
                'UTC',
                '2102-12-02T01:12:01+03:00',
            ],
            'datetime null + Europe/Moscow' => [
                '2102-12-01T22:12:01.000000+00:00',
                null,
                '2102-12-02T01:12:01+03:00',
            ],
        ];
    }
    // </editor-fold>
}
