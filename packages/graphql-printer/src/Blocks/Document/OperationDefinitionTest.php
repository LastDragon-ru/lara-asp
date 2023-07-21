<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(OperationDefinition::class)]
class OperationDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSerialize
     *
     * @param (TypeNode&Node)|Type|null $type
     */
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        OperationDefinitionNode $definition,
        TypeNode|Type|null $type,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new OperationDefinition($context, $definition, $type))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::operationDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $schema     = BuildSchema::build(
            <<<'STRING'
            type Query {
                field(a: Int): A
            }

            type A {
                a: String
                b: Boolean
            }
            STRING,
        );
        $context    = new Context(new TestSettings(), null, $schema);
        $collector  = new Collector();
        $definition = Parser::operationDefinition('query($a: Int) @a { field(a: $a) { a } }');
        $type       = $schema->getType('Query');
        $block      = new OperationDefinition($context, $definition, $type);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(
            [
                'Query'  => 'Query',
                'A'      => 'A',
                'String' => 'String',
                'Int'    => 'Int',
            ],
            $collector->getUsedTypes(),
        );
        self::assertEquals(['@a' => '@a'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new OperationDefinition($context, Parser::operationDefinition($content), $type);

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }

    public function testStatisticsNoSchema(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::operationDefinition('query($a: Int) @a { field(a: $a) { a } }');
        $type       = null;
        $block      = new OperationDefinition($context, $definition, $type);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['Int' => 'Int'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new OperationDefinition($context, Parser::operationDefinition($content), $type);

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{
     *      string,
     *      Settings,
     *      int,
     *      int,
     *      OperationDefinitionNode,
     *      (TypeNode&Node)|Type|null,
     *      ?Schema,
     *      }>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(false)
            ->setNormalizeFields(false)
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);

        return [
            'without variables'               => [
                <<<'STRING'
                query test
                @a
                {
                    b
                    a
                }
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::operationDefinition(
                    'query test @a { b a }',
                ),
                null,
                null,
            ],
            'with variables (short)'          => [
                <<<'STRING'
                mutation test($a: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa"], $b: Int)
                @a
                {
                    b
                    a
                }
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::operationDefinition(
                    'mutation test($a: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa"], $b: Int) @a { b a }',
                ),
                null,
                null,
            ],
            'with variables (long)'           => [
                <<<'STRING'
                query test(
                    $a: [String!] = [
                        "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                    ]
                    $b: Int
                )
                @a
                {
                    b
                    a
                }
                STRING,
                $settings
                    ->setPrintDirectives(true)
                    ->setLineLength(51),
                0,
                120,
                Parser::operationDefinition(
                    'query test($a: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa"], $b: Int) @a { b a }',
                ),
                null,
                null,
            ],
            'normalized'                      => [
                <<<'STRING'
                query($a: String, $b: Int)
                @a
                {
                    a
                    b
                }
                STRING,
                $settings
                    ->setPrintDirectives(true)
                    ->setNormalizeFields(true)
                    ->setNormalizeArguments(true),
                0,
                0,
                Parser::operationDefinition(
                    'query($b: Int, $a: String) @a { a b }',
                ),
                null,
                null,
            ],
            'with variables always multiline' => [
                <<<'STRING'
                query(
                    $b: Int
                    $a: String
                ) {
                    a
                    b
                }
                STRING,
                $settings
                    ->setPrintDirectives(false)
                    ->setAlwaysMultilineArguments(true),
                0,
                0,
                Parser::operationDefinition(
                    'query($b: Int, $a: String) @a { a b }',
                ),
                null,
                null,
            ],
            'indent'                          => [
                <<<'STRING'
                query test($b: Int, $a: String) {
                        a
                        b
                    }
                STRING,
                $settings,
                1,
                0,
                Parser::operationDefinition(
                    'query test($b: Int, $a: String) { a b }',
                ),
                null,
                null,
            ],
            'filter (no schema)'              => [
                <<<'STRING'
                query test($b: Int, $a: String)
                @a
                {
                    a
                    b
                }
                STRING,
                $settings
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static fn () => false)
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::operationDefinition(
                    'query test($b: Int, $a: String) @a @b { a b }',
                ),
                Type::int(),
                null,
            ],
            'filter'                          => [
                '',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::operationDefinition(
                    'query test($b: Int, $a: String) @a @b { a b }',
                ),
                Parser::typeReference('Query'),
                BuildSchema::build(
                    <<<'STRING'
                    type A {
                        test: String
                    }
                    STRING,
                ),
            ],
            'filter: type'                    => [
                <<<'STRING'
                query test($a: String)
                @a
                {
                    a
                }
                STRING,
                $settings
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Int';
                    }),
                0,
                0,
                Parser::operationDefinition(
                    'query test($b: Int, $a: String) @a(a: 123) { a b }',
                ),
                Parser::typeReference('Query'),
                BuildSchema::build(
                    <<<'STRING'
                    type Query {
                        a: A
                        b: Int
                    }

                    type A {
                        a: Int
                        b: String
                    }

                    directive @a(a: Int) on FIELD
                    STRING,
                ),
            ],
            'filter: operation'               => [
                <<<'STRING'
                query test($a: String)
                @a
                {
                    a
                }
                STRING,
                $settings
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Int';
                    }),
                0,
                0,
                Parser::operationDefinition(
                    'query test($b: Int, $a: String) @a(a: 123) { a b }',
                ),
                null,
                BuildSchema::build(
                    <<<'STRING'
                    type Query {
                        a: A
                        b: Int
                    }

                    type A {
                        a: Int
                        b: String
                    }

                    directive @a(a: Int) on FIELD
                    STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
