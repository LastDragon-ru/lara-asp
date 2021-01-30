<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Spa\Testing\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Validation\Rules\DateRule
 */
class DateRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::passes
     *
     * @dataProvider dataProviderPasses
     *
     * @param bool   $expected
     * @param string $value
     *
     * @return void
     */
    public function testPasses(bool $expected, string $value): void {
        $translator = $this->app->make(Translator::class);
        $rule       = new DateRule($translator);

        $this->assertEquals($expected, $rule->passes('attribute', $value));
    }

    /**
     * @covers ::message
     */
    public function testMessage(): void {
        $translator = $this->app->make(Translator::class);
        $rule       = new DateRule($translator);

        $this->assertEquals('The :attribute is not a valid date.', $rule->message());
    }

    /**
     * @covers ::getValue
     *
     * @dataProvider dataProviderGetValue
     *
     * @param string|false $expected
     * @param string       $value
     *
     * @return void
     */
    public function testGetValue($expected, string $value): void {
        if (!$expected) {
            $this->expectExceptionObject(new InvalidFormatException('Trailing data'));
        }

        $translator = $this->app->make(Translator::class);
        $rule       = new DateRule($translator);
        $date       = $rule->getValue($value);

        $this->assertEquals($expected, $date ? $date->format('Y-m-d\TH:i:s.uP') : null);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderPasses(): array {
        return [
            'valid date'   => [true, '2102-12-01'],
            'invalid date' => [false, '02-12-01'],
            'datetime'     => [false, '2102-12-01T22:12:01'],
        ];
    }

    public function dataProviderGetValue(): array {
        return [
            'valid date'   => ['2102-12-01T00:00:00.000000+00:00', '2102-12-01'],
            'invalid date' => ['0002-12-01T00:00:00.000000+00:00', '02-12-01'],
            'datetime'     => [false, '2102-12-01T00:00:00'],
        ];
    }
    // </editor-fold>
}
