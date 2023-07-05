<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\DirectiveLocation as GraphQLDirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DirectiveDefinition::class)]
#[CoversClass(ArgumentsDefinition::class)]
#[CoversClass(DirectiveLocations::class)]
#[CoversClass(DirectiveLocation::class)]
class DirectiveDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSerialize
     */
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        DirectiveDefinitionNode|Directive $definition,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (new DirectiveDefinition($context, $definition))->serialize($level, $used);

        if ($expected) {
            Parser::directiveDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
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
                GraphQLDirectiveLocation::FIELD,
            ],
        ]);
        $block      = new DirectiveDefinition($context, $definition);
        $content    = $block->serialize(0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['B' => 'B'], $block->getUsedTypes());
        self::assertEquals([], $block->getUsedDirectives());

        $ast = new DirectiveDefinition($context, Parser::directiveDefinition($content));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, DirectiveDefinitionNode|Directive}>
     */
    public static function dataProviderSerialize(): array {
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                        GraphQLDirectiveLocation::ENUM,
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                        GraphQLDirectiveLocation::ENUM,
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                        GraphQLDirectiveLocation::ENUM,
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                        GraphQLDirectiveLocation::ENUM,
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                        GraphQLDirectiveLocation::ENUM,
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                        GraphQLDirectiveLocation::ENUM,
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                        GraphQLDirectiveLocation::ENUM,
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
                        GraphQLDirectiveLocation::OBJECT,
                        GraphQLDirectiveLocation::ENUM,
                        GraphQLDirectiveLocation::INPUT_FIELD_DEFINITION,
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
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
                        GraphQLDirectiveLocation::ENUM,
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
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                    ],
                ]),
            ],
            'filter'                     => [
                '',
                $settings
                    ->setDirectiveDefinitionFilter(static fn () => false),
                0,
                0,
                new Directive([
                    'name'      => 'test',
                    'locations' => [
                        GraphQLDirectiveLocation::ARGUMENT_DEFINITION,
                    ],
                ]),
            ],
            'ast'                        => [
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
                Parser::directiveDefinition(
                    'directive @test(a: String) repeatable on ARGUMENT_DEFINITION | ENUM',
                ),
            ],
            'ast + filter'               => [
                '',
                $settings
                    ->setDirectiveDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::directiveDefinition(
                    'directive @test on ARGUMENT_DEFINITION',
                ),
            ],
        ];
    }
    // </editor-fold>
}
