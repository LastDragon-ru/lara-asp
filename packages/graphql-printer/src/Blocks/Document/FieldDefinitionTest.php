<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\TestCase;
use LastDragon_ru\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(FieldDefinition::class)]
#[CoversClass(ArgumentsDefinition::class)]
final class FieldDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        FieldDefinitionNode|GraphQLFieldDefinition $definition,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new FieldDefinition($context, $definition))->serialize($collector, $level, $used);

        Parser::fieldDefinition($actual);

        self::assertSame($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = new GraphQLFieldDefinition([
            'name'    => 'A',
            'type'    => new NonNull(
                new ObjectType([
                    'name'   => 'A',
                    'fields' => static function () use (&$definition): array {
                        return [$definition];
                    },
                ]),
            ),
            'astNode' => Parser::fieldDefinition('a: A @a'),
        ]);
        $block      = new FieldDefinition($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new FieldDefinition($context, Parser::fieldDefinition($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, FieldDefinitionNode|GraphQLFieldDefinition}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);
        $object   = new ObjectType([
            'name'   => 'Test',
            'fields' => [
                'field' => [
                    'type' => Type::int(),
                ],
            ],
        ]);

        return [
            'without args'               => [
                <<<'STRING'
                """
                Description
                """
                test: Test!
                @a
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name'        => 'test',
                    'type'        => new NonNull($object),
                    'astNode'     => Parser::fieldDefinition('test: Test! @a'),
                    'description' => 'Description',
                ]),
            ],
            'with args (short)'          => [
                <<<'STRING'
                """
                Description
                """
                test(a: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa"], b: Int): Test!
                STRING,
                $settings,
                0,
                0,
                new GraphQLFieldDefinition([
                    'name'        => 'test',
                    'type'        => new NonNull($object),
                    'args'        => [
                        'a' => [
                            'type'         => new ListOfType(new NonNull(Type::string())),
                            'defaultValue' => [
                                'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                            ],
                        ],
                        'b' => [
                            'type' => Type::int(),
                        ],
                    ],
                    'description' => 'Description',
                ]),
            ],
            'with args (long)'           => [
                <<<'STRING'
                test(
                    b: Int

                    """
                    Description
                    """
                    a: String! = "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                ): Test!
                STRING,
                $settings,
                0,
                0,
                new GraphQLFieldDefinition([
                    'name' => 'test',
                    'type' => new NonNull($object),
                    'args' => [
                        'b' => [
                            'type' => Type::int(),
                        ],
                        'a' => [
                            'type'         => new NonNull(Type::string()),
                            'description'  => 'Description',
                            'defaultValue' => 'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                        ],
                    ],
                ]),
            ],
            'with args normalized'       => [
                <<<'STRING'
                test(a: String, b: Int): Test!
                STRING,
                $settings->setNormalizeArguments(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name' => 'test',
                    'type' => new NonNull($object),
                    'args' => [
                        'b' => [
                            'type' => Type::int(),
                        ],
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'with args always multiline' => [
                <<<'STRING'
                test(
                    b: Int
                    a: String
                ): Test!
                STRING,
                $settings->setAlwaysMultilineArguments(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name' => 'test',
                    'type' => new NonNull($object),
                    'args' => [
                        'b' => [
                            'type' => Type::int(),
                        ],
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'indent'                     => [
                <<<'STRING'
                test(
                        a: String
                        b: Int
                    ): Test!
                STRING,
                $settings->setNormalizeArguments(true),
                1,
                120,
                new GraphQLFieldDefinition([
                    'name' => 'test',
                    'type' => new NonNull($object),
                    'args' => [
                        'b' => [
                            'type' => Type::int(),
                        ],
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'deprecationReason (empty)'  => [
                <<<'STRING'
                test: Test!
                @deprecated
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name'              => 'test',
                    'type'              => new NonNull($object),
                    'deprecationReason' => '',
                ]),
            ],
            'deprecationReason'          => [
                <<<'STRING'
                test: Test!
                @deprecated(reason: "test")
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new GraphQLFieldDefinition([
                    'name'              => 'test',
                    'type'              => new NonNull($object),
                    'deprecationReason' => 'test',
                    'astNode'           => Parser::fieldDefinition(
                        'test: Test! @deprecated(reason: "should be ignored")',
                    ),
                ]),
            ],
            'ast'                        => [
                <<<'STRING'
                test(
                    """
                    Description
                    """
                    a: String! = "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                ): Test!
                @a
                STRING,
                $settings,
                0,
                0,
                Parser::fieldDefinition(
                    'test("Description" a: String! = "aaaaaaaaaaaaaaaaaaaaaaaaaa"): Test! @a',
                ),
            ],
        ];
    }
    // </editor-fold>
}
