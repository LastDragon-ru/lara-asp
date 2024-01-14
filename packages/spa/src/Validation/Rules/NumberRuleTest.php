<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use const INF;
use const NAN;

/**
 * @internal
 */
#[CoversClass(NumberRule::class)]
final class NumberRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPasses
     */
    public function testPasses(bool $expected, mixed $value): void {
        $translator = Container::getInstance()->make(Translator::class);
        $rule       = new NumberRule($translator);

        self::assertEquals($expected, $rule->passes('attribute', $value));
    }

    public function testMessage(): void {
        $translator = Container::getInstance()->make(Translator::class);
        $rule       = new NumberRule($translator);

        self::assertEquals('The :attribute is not an number.', $rule->message());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderPasses(): array {
        return [
            'true'  => [false, true],
            'false' => [false, false],
            '0'     => [true, 0],
            '1'     => [true, 1],
            '"0"'   => [false, '0'],
            '"1"'   => [false, '1'],
            'float' => [true, 123.23],
            '+inf'  => [false, INF],
            '-inf'  => [false, -INF],
            'nan'   => [false, NAN],
        ];
    }
    // </editor-fold>
}
