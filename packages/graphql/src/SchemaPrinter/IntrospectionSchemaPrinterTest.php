<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\IntrospectionSchemaPrinter
 */
class IntrospectionSchemaPrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPrint
     */
    public function testPrint(string $expected, Settings $settings, int $level): void {
        $expected = $this->getTestData()->content($expected);
        $printer  = $this->app->make(IntrospectionSchemaPrinter::class)->setSettings($settings)->setLevel($level);
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
