<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Exception;
use Illuminate\Contracts\Translation\Translator;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;

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
     */
    public function testPasses(bool $expected, string $value): void {
        $translator = $this->app->make(Translator::class);
        $rule       = new DateRule($translator);

        self::assertEquals($expected, $rule->passes('attribute', $value));
    }

    /**
     * @covers ::message
     */
    public function testMessage(): void {
        $translator = $this->app->make(Translator::class);
        $rule       = new DateRule($translator);

        self::assertEquals('The :attribute is not a valid date.', $rule->message());
    }

    /**
     * @covers ::getValue
     *
     * @dataProvider dataProviderGetValue
     */
    public function testGetValue(Exception|string|null $expected, string $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $translator = $this->app->make(Translator::class);
        $rule       = new DateRule($translator);
        $date       = $rule->getValue($value);

        self::assertEquals($expected, $date ? $date->format('Y-m-d\TH:i:s.uP') : null);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderPasses(): array {
        return [
            'valid date'   => [true, '2102-12-01'],
            'invalid date' => [false, '02-12-01'],
            'datetime'     => [false, '2102-12-01T22:12:01'],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderGetValue(): array {
        return [
            'valid date'   => ['2102-12-01T00:00:00.000000+00:00', '2102-12-01'],
            'invalid date' => ['0002-12-01T00:00:00.000000+00:00', '02-12-01'],
            'datetime'     => [new InvalidArgumentException('Trailing data'), '2102-12-01T00:00:00'],
        ];
    }
    // </editor-fold>
}
