<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\DirectiveLocation as GraphQLDirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Directive::class)]
final class DirectiveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        DirectiveNode $node,
        ?GraphQLDirective $directive,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $resolver  = $directive ? $this->getDirectiveResolver($directive) : null;
        $context   = new Context($settings, $resolver, $schema);
        $actual    = (new Directive($context, $node))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::directive($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $directive = new GraphQLDirective([
            'name'      => 'test',
            'args'      => [
                'a' => [
                    'type' => new InputObjectType([
                        'name'   => 'A',
                        'fields' => [
                            'a' => [
                                'type' => Type::string(),
                            ],
                        ],
                    ]),
                ],
                'b' => [
                    'type' => Type::string(),
                ],
            ],
            'locations' => [
                GraphQLDirectiveLocation::FIELD,
            ],
        ]);
        $resolver  = $this->getDirectiveResolver($directive);
        $context   = new Context(new TestSettings(), $resolver, null);
        $collector = new Collector();
        $node      = Parser::directive('@test(a: 123, b: "b")');
        $block     = new Directive($context, $node);
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A', 'String' => 'String'], $collector->getUsedTypes());
        self::assertEquals(['@test' => '@test'], $collector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    private function getDirectiveResolver(GraphQLDirective $directive): DirectiveResolver {
        return new class($directive) implements DirectiveResolver {
            public function __construct(
                protected GraphQLDirective $directive,
            ) {
                // empty
            }

            #[Override]
            public function getDefinition(string $name): ?GraphQLDirective {
                return $this->directive->name === $name
                    ? $this->directive
                    : null;
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getDefinitions(): array {
                return [];
            }
        };
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, DirectiveNode, ?GraphQLDirective, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);

        return [
            'without arguments'                 => [
                '@directive',
                $settings,
                0,
                0,
                Parser::directive('@directive'),
                null,
                null,
            ],
            'without arguments (level)'         => [
                '@directive',
                $settings,
                0,
                0,
                Parser::directive('@directive'),
                null,
                null,
            ],
            'with arguments (short)'            => [
                '@directive(a: "a", b: "b")',
                $settings,
                0,
                0,
                Parser::directive('@directive(a: "a", b: "b")'),
                null,
                null,
            ],
            'with arguments (long)'             => [
                <<<'STRING'
                @directive(
                    b: "b"
                    a: "a"
                )
                STRING,
                $settings,
                0,
                120,
                Parser::directive('@directive(b: "b", a: "a")'),
                null,
                null,
            ],
            'with arguments (normalized)'       => [
                '@directive(a: "a", b: "b")',
                $settings->setNormalizeArguments(true),
                0,
                0,
                Parser::directive('@directive(b: "b", a: "a")'),
                null,
                null,
            ],
            'with arguments (indent)'           => [
                <<<'STRING'
                @directive(
                        b: "b"
                        a: "a"
                    )
                STRING,
                $settings,
                1,
                120,
                Parser::directive('@directive(b: "b", a: "a")'),
                null,
                null,
            ],
            'with arguments (always multiline)' => [
                <<<'STRING'
                @directive(
                    a: "a"
                )
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true),
                0,
                0,
                Parser::directive('@directive(a: "a")'),
                null,
                null,
            ],
            'arguments indent'                  => [
                <<<'STRING'
                @directive(
                    a: ["aaaaaaaaaaaaaaaaaaaaaaaaaa", "aaaaaaaaaaaaaaaaaaaaaaaaaa"]
                    b: {
                        a: "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                        b: ["aaaaaaaaaaaaaaaaaaaaaaaaaa"]
                    }
                )
                STRING,
                $settings
                    ->setLineLength(80)
                    ->setAlwaysMultilineArguments(true),
                0,
                120,
                Parser::directive(
                    <<<'STRING'
                    @directive(
                        a: ["aaaaaaaaaaaaaaaaaaaaaaaaaa", "aaaaaaaaaaaaaaaaaaaaaaaaaa"]
                        b: {
                            a: "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                            b: ["aaaaaaaaaaaaaaaaaaaaaaaaaa"]
                        }
                    )
                    STRING,
                ),
                null,
                null,
            ],
            'filter: directive'                 => [
                '',
                $settings
                    ->setDirectiveFilter(static fn () => false),
                0,
                0,
                Parser::directive(
                    '@directive',
                ),
                null,
                null,
            ],
            'filter: type (no schema)'          => [
                <<<'STRING'
                @directive(
                    a: 123
                    b: "b"
                )
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'String';
                    }),
                0,
                0,
                Parser::directive(
                    '@directive(a: 123, b: "b")',
                ),
                new GraphQLDirective([
                    'name'      => 'directive',
                    'args'      => [
                        'a' => [
                            'type' => Type::int(),
                        ],
                        'b' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations' => [
                        GraphQLDirectiveLocation::FIELD,
                    ],
                ]),
                null,
            ],
            'filter: type'                      => [
                <<<'STRING'
                @directive(
                    a: 123
                )
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'String';
                    }),
                0,
                0,
                Parser::directive(
                    '@directive(a: 123, b: "b")',
                ),
                new GraphQLDirective([
                    'name'      => 'directive',
                    'args'      => [
                        'a' => [
                            'type' => Type::int(),
                        ],
                        'b' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations' => [
                        GraphQLDirectiveLocation::FIELD,
                    ],
                ]),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    scalar A
                    scalar B
                    GRAPHQL,
                ),
            ],
        ];
    }
    // </editor-fold>
}
