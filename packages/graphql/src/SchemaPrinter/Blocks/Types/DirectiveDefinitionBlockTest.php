<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\DirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\DirectiveDefinitionBlock
 */
class DirectiveDefinitionBlockTest extends TestCase {
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
        Directive $definition,
    ): void {
        $settings = new BlockSettings($this->app->make(DirectiveResolver::class), $settings);
        $actual   = (string) (new DirectiveDefinitionBlock($settings, $level, $used, $definition));

        Parser::directiveDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::__toString
     */
    public function testStatistics(): void {
        $settings   = new TestSettings();
        $settings   = new BlockSettings($this->app->make(DirectiveResolver::class), $settings);
        $definition = new Directive([
            'name'      => 'A',
            'args'      => [
                'a' => [
                    'type' => new ObjectType(['name' => 'B']),
                ],
            ],
            'locations' => [
                DirectiveLocation::FIELD,
            ],
        ]);
        $block      = new DirectiveDefinitionBlock($settings, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['B' => 'B'], $block->getUsedTypes());
        self::assertEquals([], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, Directive}>
     */
    public function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setAlwaysMultilineDirectiveLocations(false);

        return [
            'description'                => [
                <<<'STRING'
                """
                Description
                """
                directive @test on ARGUMENT_DEFINITION | ENUM
                STRING,
                $settings,
                0,
                0,
                new Directive([
                    'name'        => 'test',
                    'description' => 'Description',
                    'locations'   => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'repeatable'                 => [
                <<<'STRING'
                directive @test repeatable on ARGUMENT_DEFINITION | ENUM
                STRING,
                $settings,
                0,
                0,
                new Directive([
                    'name'         => 'test',
                    'locations'    => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                    'isRepeatable' => true,
                ]),
            ],
            'args'                       => [
                <<<'STRING'
                directive @test(a: String) repeatable on ARGUMENT_DEFINITION | ENUM
                STRING,
                $settings,
                0,
                0,
                new Directive([
                    'name'         => 'test',
                    'args'         => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations'    => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                    'isRepeatable' => true,
                ]),
            ],
            'multiline + repeatable'     => [
                <<<'STRING'
                directive @test(
                    a: String
                )
                repeatable on
                    | ARGUMENT_DEFINITION
                    | ENUM
                STRING,
                $settings,
                0,
                120,
                new Directive([
                    'name'         => 'test',
                    'args'         => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations'    => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                    'isRepeatable' => true,
                ]),
            ],
            'multiline'                  => [
                <<<'STRING'
                directive @test(
                    a: String
                )
                on
                    | ARGUMENT_DEFINITION
                    | ENUM
                STRING,
                $settings,
                0,
                120,
                new Directive([
                    'name'      => 'test',
                    'args'      => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations' => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'multiline (no args)'        => [
                <<<'STRING'
                directive @test on
                    | ARGUMENT_DEFINITION
                    | ENUM
                STRING,
                $settings,
                0,
                60,
                new Directive([
                    'name'      => 'test',
                    'locations' => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'indent'                     => [
                <<<'STRING'
                directive @test(
                        a: String
                    )
                    on
                        | ARGUMENT_DEFINITION
                        | ENUM
                STRING,
                $settings,
                1,
                120,
                new Directive([
                    'name'      => 'test',
                    'args'      => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations' => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'normalized'                 => [
                <<<'STRING'
                directive @test on ENUM | INPUT_FIELD_DEFINITION | OBJECT
                STRING,
                $settings
                    ->setNormalizeDirectiveLocations(true),
                0,
                0,
                new Directive([
                    'name'      => 'test',
                    'locations' => [
                        DirectiveLocation::OBJECT,
                        DirectiveLocation::ENUM,
                        DirectiveLocation::INPUT_FIELD_DEFINITION,
                    ],
                ]),
            ],
            'locations always multiline' => [
                <<<'STRING'
                directive @test on
                    | ARGUMENT_DEFINITION
                STRING,
                $settings
                    ->setAlwaysMultilineDirectiveLocations(true),
                0,
                0,
                new Directive([
                    'name'      => 'test',
                    'locations' => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
