<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\EnumValueDefinition
 */
class EnumValueDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        GraphQLEnumValueDefinition $type,
    ): void {
        $actual = (string) (new EnumValueDefinition($settings, $level, $used, $type));

        Parser::enumValueDefinition($actual);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, GraphQLEnumValueDefinition}>
     */
    public function dataProviderToString(): array {
        $settings = new TestSettings();

        return [
            'value'  => [
                <<<'STRING'
                A
                STRING,
                $settings,
                0,
                0,
                new GraphQLEnumValueDefinition([
                    'name'  => 'A',
                    'value' => 'A',
                ]),
            ],
            'indent' => [
                <<<'STRING'
                A
                STRING,
                $settings,
                1,
                0,
                new GraphQLEnumValueDefinition([
                    'name'  => 'A',
                    'value' => 'A',
                ]),
            ],
        ];
    }
    // </editor-fold>
}
