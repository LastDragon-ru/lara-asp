<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
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
#[CoversClass(InputObjectTypeDefinition::class)]
#[CoversClass(InputFieldsDefinition::class)]
final class InputObjectTypeDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        InputObjectTypeDefinitionNode|InputObjectType $definition,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new InputObjectTypeDefinition($context, $definition))->serialize($collector, $level, $used);

        if ($expected !== '') {
            Parser::inputObjectTypeDefinition($actual);
        }

        self::assertSame($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = new InputObjectType([
            'name'    => 'A',
            'fields'  => [
                'b' => [
                    'name'    => 'b',
                    'type'    => new InputObjectType([
                        'name'   => 'B',
                        'fields' => [
                            'field' => [
                                'type' => Type::string(),
                            ],
                        ],
                    ]),
                    'astNode' => Parser::inputValueDefinition('b: B @a'),
                ],
            ],
            'astNode' => Parser::inputObjectTypeDefinition('input A @b'),
        ]);
        $block      = new InputObjectTypeDefinition($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A', 'B' => 'B'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new InputObjectTypeDefinition($context, Parser::inputObjectTypeDefinition($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InputObjectTypeDefinitionNode|InputObjectType}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false);

        return [
            'description + directives'          => [
                <<<'GRAPHQL'
                """
                Description
                """
                input Test
                @a
                @b
                @c
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new InputObjectType([
                    'name'              => 'Test',
                    'fields'            => [],
                    'astNode'           => Parser::inputObjectTypeDefinition('input Test @a'),
                    'description'       => 'Description',
                    'extensionASTNodes' => [
                        Parser::inputObjectTypeExtension('extend input Test @b'),
                        Parser::inputObjectTypeExtension('extend input Test @c'),
                    ],
                ]),
            ],
            'description + directives + fields' => [
                <<<'GRAPHQL'
                """
                Description
                """
                input Test
                @a
                {
                    c: C

                    """
                    Description
                    """
                    b: B

                    a: A
                }
                GRAPHQL,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InputObjectType([
                    'name'        => 'Test',
                    'astNode'     => Parser::inputObjectTypeDefinition('input Test @a'),
                    'description' => 'Description',
                    'fields'      => [
                        'c' => [
                            'type' => new InputObjectType([
                                'name'   => 'C',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                        ],
                        'b' => [
                            'type'        => new InputObjectType([
                                'name'   => 'B',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                            'description' => 'Description',
                        ],
                        'a' => [
                            'type' => new InputObjectType([
                                'name'   => 'A',
                                'fields' => [
                                    'field' => [
                                        'type' => Type::string(),
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ]),
            ],
            'fields'                            => [
                <<<'GRAPHQL'
                input Test {
                    a: String
                }
                GRAPHQL,
                $settings,
                0,
                0,
                new InputObjectType([
                    'name'   => 'Test',
                    'fields' => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'indent'                            => [
                <<<'GRAPHQL'
                input Test {
                        a: String
                    }
                GRAPHQL,
                $settings->setNormalizeInterfaces(true),
                1,
                120,
                new InputObjectType([
                    'name'   => 'Test',
                    'fields' => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'filter'                            => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                new InputObjectType([
                    'name'   => 'Test',
                    'fields' => [],
                ]),
            ],
            'ast'                               => [
                <<<'GRAPHQL'
                """
                Description
                """
                input Test
                @a
                {
                    a: String
                }
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true)
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::inputObjectTypeDefinition(
                    '"Description" input Test @a @b { a: String }',
                ),
            ],
            'ast + filter'                      => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::inputObjectTypeDefinition(
                    'input Test @a { a: String }',
                ),
            ],
        ];
    }
    // </editor-fold>
}
