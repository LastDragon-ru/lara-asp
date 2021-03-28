<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Validation\Rules\DateTimeRule
 */
class DateTimeRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::passes
     *
     * @dataProvider dataProviderPasses
     */
    public function testPasses(bool $expected, string $value): void {
        $translator = $this->app->make(Translator::class);
        $config     = $this->app->make(Repository::class);
        $rule       = new DateTimeRule($translator, $config);

        $this->assertEquals($expected, $rule->passes('attribute', $value));
    }

    /**
     * @covers ::message
     */
    public function testMessage(): void {
        $translator = $this->app->make(Translator::class);
        $config     = $this->app->make(Repository::class);
        $rule       = new DateTimeRule($translator, $config);

        $this->assertEquals('The :attribute is not a valid datetime.', $rule->message());
    }

    /**
     * @covers ::getValue
     *
     * @dataProvider dataProviderGetValue
     */
    public function testGetValue(string|Exception $expected, ?string $tz, string $value): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $translator = $this->app->make(Translator::class);
        $config     = $this->app->make(Repository::class);
        $rule       = new DateTimeRule($translator, $config);

        $config->set('app.timezone', $tz);

        $date = $rule->getValue($value);

        $this->assertEquals($expected, $date ? $date->format('Y-m-d\TH:i:s.uP') : null);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderPasses(): array {
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
    public function dataProviderGetValue(): array {
        return [
            'date'                          => [
                new InvalidArgumentException('Data missing'),
                null,
                '2102-12-01',
            ],
            'invalid date'                  => [
                new InvalidArgumentException('Data missing'),
                null,
                '02-12-01',
            ],
            'datetime without timezone'     => [
                new InvalidArgumentException('Data missing'),
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
