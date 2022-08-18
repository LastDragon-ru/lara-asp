<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\ScalarType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\ScalarTypeDefinitionBlock
 */
class ScalarTypeDefinitionBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        ScalarType $type,
    ): void {
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $actual   = (string) (new ScalarTypeDefinitionBlock($settings, $level, $used, $type));

        Parser::scalarTypeDefinition($actual);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ScalarType}>
     */
    public function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setAlwaysMultilineArguments(false)
            ->setPrintDirectives(false);

        return [
            'scalar'                          => [
                <<<'STRING'
                scalar Test
                STRING,
                $settings,
                0,
                0,
                new CustomScalarType([
                    'name' => 'Test',
                ]),
            ],
            'with description and directives' => [
                <<<'STRING'
                """
                Description

                ```graphql
                @a
                ```
                """
                scalar Test
                @a
                STRING,
                $settings
                    ->setPrintDirectivesInDescription(true)
                    ->setPrintDirectives(true),
                0,
                0,
                new CustomScalarType([
                    'name'        => 'Test',
                    'description' => 'Description',
                    'astNode'     => Parser::scalarTypeDefinition(
                        <<<'STRING'
                        scalar Test @a
                        STRING,
                    ),
                ]),
            ],
            'with directives in description'  => [
                <<<'STRING'
                """
                Description

                ```graphql
                @a
                ```
                """
                scalar Test
                STRING,
                $settings->setPrintDirectivesInDescription(true),
                0,
                0,
                new CustomScalarType([
                    'name'        => 'Test',
                    'description' => 'Description',
                    'astNode'     => Parser::scalarTypeDefinition(
                        <<<'STRING'
                        scalar Test @a
                        STRING,
                    ),
                ]),
            ],
            'indent'                          => [
                <<<'STRING'
                """
                    Description
                    """
                    scalar Test
                    @a(
                        value: "very very long value"
                    )
                    @b(value: "b")
                STRING,
                $settings->setPrintDirectives(true),
                1,
                60,
                new CustomScalarType([
                    'name'        => 'Test',
                    'description' => 'Description',
                    'astNode'     => Parser::scalarTypeDefinition(
                        <<<'STRING'
                        scalar Test @a(value: "very very long value") @b(value: "b")
                        STRING,
                    ),
                ]),
            ],
            'indent + no description'         => [
                <<<'STRING'
                scalar Test
                    @a(
                        value: "very very long value"
                    )
                    @b(value: "b")
                STRING,
                $settings->setPrintDirectives(true),
                1,
                60,
                new CustomScalarType([
                    'name'    => 'Test',
                    'astNode' => Parser::scalarTypeDefinition(
                        <<<'STRING'
                        scalar Test @a(value: "very very long value") @b(value: "b")
                        STRING,
                    ),
                ]),
            ],
        ];
    }
    // </editor-fold>
}
