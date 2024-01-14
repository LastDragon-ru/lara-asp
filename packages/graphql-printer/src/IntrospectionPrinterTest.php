<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(IntrospectionPrinter::class)]
final class IntrospectionPrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderPrint
     */
    public function testPrint(string $expected, Settings $settings, int $level): void {
        $expected = self::getTestData()->content($expected);
        $printer  = (new IntrospectionPrinter())->setSettings($settings);
        $schema   = new Schema([]);
        $actual   = $printer->print($schema, $level);

        self::assertEquals($expected, (string) $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, Settings, int}>
     */
    public static function dataProviderPrint(): array {
        return [
            GraphQLSettings::class         => [
                '~GraphQLSettings.graphql',
                new GraphQLSettings(),
                0,
            ],
            TestSettings::class            => [
                '~TestSettings.graphql',
                new TestSettings(),
                0,
            ],
            TestSettings::class.' (level)' => [
                '~TestSettings-level.graphql',
                new TestSettings(),
                1,
            ],
        ];
    }
    // </editor-fold>
}
