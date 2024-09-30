<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\DirectiveLocation as GraphQLDirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(DirectiveDefinition::class)]
#[CoversClass(ArgumentsDefinition::class)]
#[CoversClass(DirectiveLocations::class)]
#[CoversClass(DirectiveLocation::class)]
final class DirectiveDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        DirectiveDefinitionNode|Directive $definition,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new DirectiveDefinition($context, $definition))->serialize($collector, $level, $used);

        if ($expected !== '') {
            Parser::directiveDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
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
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['B' => 'B'], $collector->getUsedTypes());
        self::assertEquals(['@A' => '@A'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new DirectiveDefinition($context, Parser::directiveDefinition($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
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
                <<<'GRAPHQL'
                """
                Description
                """
                directive @test on ARGUMENT_DEFINITION | ENUM
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test repeatable on ARGUMENT_DEFINITION | ENUM
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test(a: String) repeatable on ARGUMENT_DEFINITION | ENUM
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test(
                    a: String
                )
                repeatable on
                    | ARGUMENT_DEFINITION
                    | ENUM
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test(
                    a: String
                )
                on
                    | ARGUMENT_DEFINITION
                    | ENUM
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test
                on
                    | ARGUMENT_DEFINITION
                    | ENUM
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test(
                        a: String
                    )
                    on
                        | ARGUMENT_DEFINITION
                        | ENUM
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test on ENUM | INPUT_FIELD_DEFINITION | OBJECT
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test
                on
                    | ARGUMENT_DEFINITION
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test(
                    a: String
                )
                on
                    | ENUM
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test(
                    """
                    Description
                    """
                    a: String
                )
                on
                    | ARGUMENT_DEFINITION
                GRAPHQL,
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
                <<<'GRAPHQL'
                directive @test(
                    a: String
                )
                repeatable on
                    | ARGUMENT_DEFINITION
                    | ENUM
                GRAPHQL,
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
