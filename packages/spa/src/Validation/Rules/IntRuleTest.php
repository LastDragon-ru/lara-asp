<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Validation\Factory;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use const INF;
use const NAN;

/**
 * @internal
 */
#[CoversClass(IntRule::class)]
final class IntRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderIsValid')]
    public function testRule(bool $expected, mixed $value): void {
        $rule      = $this->app()->make(IntRule::class);
        $factory   = $this->app()->make(Factory::class);
        $validator = $factory->make(['value' => $value], ['value' => $rule]);

        self::assertEquals($expected, !$validator->fails());

        if ($expected === false) {
            self::assertEquals(
                [
                    'value' => [
                        'The value is not an integer.',
                    ],
                ],
                $validator->errors()->toArray(),
            );
        }
    }

    #[DataProvider('dataProviderIsValid')]
    public function testIsValid(bool $expected, mixed $value): void {
        $rule   = $this->app()->make(IntRule::class);
        $actual = $rule->isValid('attribute', $value);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderIsValid(): array {
        return [
            'true'  => [false, true],
            'false' => [false, false],
            '0'     => [true, 0],
            '1'     => [true, 1],
            '"0"'   => [false, '0'],
            '"1"'   => [false, '1'],
            'float' => [false, 123.23],
            '+inf'  => [false, INF],
            '-inf'  => [false, -INF],
            'nan'   => [false, NAN],
        ];
    }
    // </editor-fold>
}
