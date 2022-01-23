<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\IntrospectionPrinter
 */
class IntrospectionPrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::print
     *
     * @dataProvider dataProviderPrint
     */
    public function testPrint(string $expected, Settings $settings, int $level): void {
        $expected = $this->getTestData()->content($expected);
        $printer  = (new IntrospectionPrinter())->setSettings($settings)->setLevel($level);
        $schema   = new Schema([]);
        $actual   = $printer->print($schema);

        self::assertEquals($expected, (string) $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, Settings, int}>
     */
    public function dataProviderPrint(): array {
        return [
            DefaultSettings::class         => [
                '~default-settings.graphql',
                new DefaultSettings(),
                0,
            ],
            TestSettings::class            => [
                '~test-settings.graphql',
                new TestSettings(),
                0,
            ],
            TestSettings::class.' (level)' => [
                '~test-settings-level.graphql',
                new TestSettings(),
                1,
            ],
        ];
    }
    // </editor-fold>
}
