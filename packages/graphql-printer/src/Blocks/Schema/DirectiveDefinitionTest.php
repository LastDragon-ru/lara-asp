<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Language\DirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\DirectiveDefinition
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\ArgumentsDefinition
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\DirectiveLocations
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\DirectiveLocation
 */
class DirectiveDefinitionTest extends TestCase {
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
        Directive $definition,
    ): void {
        $actual = (string) (new DirectiveDefinition($settings, $level, $used, $definition));

        Parser::directiveDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $settings   = new TestSettings();
        $definition = new Directive([
            'name'      => 'A',
            'args'      => [
                'a' => [
                    'type' => new InputObjectType([
                        'name'   => 'B',
                        'fields' => [
                            'b' => [
                                'type' => Type::string(),
                            ],
                        ],
                    ]),
                ],
            ],
            'locations' => [
                DirectiveLocation::FIELD,
            ],
        ]);
        $block      = new DirectiveDefinition($settings, 0, 0, $definition);

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
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setAlwaysMultilineArguments(false)
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
                directive @test
                on
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
                directive @test
                on
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
            'args always multiline'      => [
                <<<'STRING'
                directive @test(
                    a: String
                )
                on
                    | ENUM
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true),
                0,
                0,
                new Directive([
                    'name'      => 'test',
                    'args'      => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations' => [
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'args + one location'        => [
                <<<'STRING'
                directive @test(
                    """
                    Description
                    """
                    a: String
                )
                on
                    | ARGUMENT_DEFINITION
                STRING,
                $settings,
                0,
                0,
                new Directive([
                    'name'      => 'test',
                    'args'      => [
                        'a' => [
                            'type'        => Type::string(),
                            'description' => 'Description',
                        ],
                    ],
                    'locations' => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
