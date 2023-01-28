<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\IntrospectionPrinter
 */
class IntrospectionPrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPrint
     */
    public function testPrint(string $expected, Settings $settings, int $level): void {
        $expected = $this->getTestData()->content($expected);
        $printer  = (new IntrospectionPrinter())->setSettings($settings)->setLevel($level);
        $schema   = new Schema([]);
        $actual   = $printer->printSchema($schema);

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
            GraphQLSettings::class         => [
                '~graphql-settings.graphql',
                new GraphQLSettings(),
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
