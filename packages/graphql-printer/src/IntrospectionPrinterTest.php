<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Package\TestCase;
use LastDragon_ru\GraphQLPrinter\Settings\GraphQLSettings;
use LastDragon_ru\LaraASP\Testing\Requirements\Requirements\RequiresComposerPackage;
use LastDragon_ru\PhpUnit\GraphQL\PrinterSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(IntrospectionPrinter::class)]
final class IntrospectionPrinterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderPrint')]
    #[RequiresComposerPackage('webonyx/graphql-php', '>=15.22.0')]
    public function testPrint(string $expected, Settings $settings, int $level): void {
        $expected = self::getTestData()->content($expected);
        $printer  = (new IntrospectionPrinter())->setSettings($settings);
        $schema   = new Schema([]);
        $actual   = $printer->print($schema, $level);

        self::assertSame($expected, (string) $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, Settings, int}>
     */
    public static function dataProviderPrint(): array {
        return [
            GraphQLSettings::class            => [
                '~GraphQLSettings.graphql',
                new GraphQLSettings(),
                0,
            ],
            PrinterSettings::class            => [
                '~PrinterSettings.graphql',
                new PrinterSettings(),
                0,
            ],
            PrinterSettings::class.' (level)' => [
                '~PrinterSettings-level.graphql',
                new PrinterSettings(),
                1,
            ],
        ];
    }
    // </editor-fold>
}
