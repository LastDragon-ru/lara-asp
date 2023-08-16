<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(BoolRule::class)]
class BoolRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPasses
     */
    public function testPasses(bool $expected, mixed $value): void {
        $translator = $this->app->make(Translator::class);
        $rule       = new BoolRule($translator);

        self::assertEquals($expected, $rule->passes('attribute', $value));
    }

    public function testMessage(): void {
        $translator = $this->app->make(Translator::class);
        $rule       = new BoolRule($translator);

        self::assertEquals('The :attribute is not a boolean.', $rule->message());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderPasses(): array {
        return [
            'true'  => [true, true],
            'false' => [true, false],
            '0'     => [false, 0],
            '1'     => [false, 1],
            '"0"'   => [false, '0'],
            '"1"'   => [false, '1'],
        ];
    }
    // </editor-fold>
}
