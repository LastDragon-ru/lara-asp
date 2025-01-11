<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Exception;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Factory;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(DateRule::class)]
final class DateRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderIsValid')]
    public function testRule(bool $expected, mixed $value): void {
        $rule      = $this->app()->make(DateRule::class);
        $factory   = $this->app()->make(Factory::class);
        $validator = $factory->make(['value' => $value], ['value' => $rule]);

        self::assertSame($expected, !$validator->fails());

        if ($expected === false) {
            self::assertEquals(
                [
                    'value' => [
                        'The value is not a valid date.',
                    ],
                ],
                $validator->errors()->toArray(),
            );
        }
    }

    #[DataProvider('dataProviderIsValid')]
    public function testIsValid(bool $expected, string $value): void {
        $rule   = $this->app()->make(DateRule::class);
        $actual = $rule->isValid('attribute', $value);

        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderGetValue')]
    public function testGetValue(Exception|string|null $expected, string $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $translator = $this->app()->make(Translator::class);
        $rule       = new DateRule($translator);
        $date       = $rule->getValue($value);

        self::assertEquals($expected, $date?->format('Y-m-d\TH:i:s.uP'));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderIsValid(): array {
        return [
            'valid date'   => [true, '2102-12-01'],
            'invalid date' => [false, '02-12-01'],
            'datetime'     => [false, '2102-12-01T22:12:01'],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderGetValue(): array {
        return [
            'valid date'   => ['2102-12-01T00:00:00.000000+00:00', '2102-12-01'],
            'invalid date' => ['0002-12-01T00:00:00.000000+00:00', '02-12-01'],
            'datetime'     => [new InvalidArgumentException('Trailing data'), '2102-12-01T00:00:00'],
        ];
    }
    // </editor-fold>
}
