<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Factory;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Throwable;

use function is_array;

/**
 * @internal
 */
#[CoversClass(DateTimeRule::class)]
final class DateTimeRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderIsValid')]
    public function testRule(bool $expected, mixed $value): void {
        $rule      = Container::getInstance()->make(DateTimeRule::class);
        $factory   = Container::getInstance()->make(Factory::class);
        $validator = $factory->make(['value' => $value], ['value' => $rule]);

        self::assertEquals($expected, !$validator->fails());

        if ($expected === false) {
            self::assertEquals(
                [
                    'value' => [
                        'The value is not a valid datetime.',
                    ],
                ],
                $validator->errors()->toArray(),
            );
        }
    }

    #[DataProvider('dataProviderIsValid')]
    public function testIsValid(bool $expected, string $value): void {
        $rule   = Container::getInstance()->make(DateTimeRule::class);
        $actual = $rule->isValid('attribute', $value);

        self::assertEquals($expected, $actual);
    }

    /**
     * @param array{class: class-string<Throwable>, message: string}|string $expected
     */
    #[DataProvider('dataProviderGetValue')]
    public function testGetValue(string|array $expected, ?string $tz, string $value): void {
        if (is_array($expected)) {
            self::expectException($expected['class']);
            self::expectExceptionMessageMatches($expected['message']);
        }

        $translator = Container::getInstance()->make(Translator::class);
        $rule       = new DateTimeRule($translator);

        $this->setConfig([
            'app.timezone' => $tz,
        ]);

        $date = $rule->getValue($value);

        self::assertEquals($expected, $date ? $date->format('Y-m-d\TH:i:s.uP') : null);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderIsValid(): array {
        return [
            'valid date'                => [false, '2102-12-01'],
            'invalid date'              => [false, '02-12-01'],
            'datetime without timezone' => [false, '2102-12-01T22:12:01'],
            'datetime'                  => [true, '2102-12-01T22:12:01+00:00'],
        ];
    }

    /**
     * @return array<array-key, mixed>
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
