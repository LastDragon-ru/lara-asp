<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DateRule::class)]
final class DateRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPasses
     */
    public function testPasses(bool $expected, string $value): void {
        $translator = Container::getInstance()->make(Translator::class);
        $rule       = new DateRule($translator);

        self::assertEquals($expected, $rule->passes('attribute', $value));
    }

    public function testMessage(): void {
        $translator = Container::getInstance()->make(Translator::class);
        $rule       = new DateRule($translator);

        self::assertEquals('The :attribute is not a valid date.', $rule->message());
    }

    /**
     * @dataProvider dataProviderGetValue
     */
    public function testGetValue(Exception|string|null $expected, string $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $translator = Container::getInstance()->make(Translator::class);
        $rule       = new DateRule($translator);
        $date       = $rule->getValue($value);

        self::assertEquals($expected, $date ? $date->format('Y-m-d\TH:i:s.uP') : null);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderPasses(): array {
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
