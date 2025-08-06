<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\TestCase;
use LastDragon_ru\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function str_starts_with;

/**
 * @internal
 */
#[CoversClass(SchemaDefinition::class)]
#[CoversClass(RootOperationTypeDefinition::class)]
#[CoversClass(RootOperationTypesDefinition::class)]
final class SchemaDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        SchemaDefinitionNode|Schema $definition,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new SchemaDefinition($context, $definition))->serialize($collector, $level, $used);

        if ($expected !== '' && !str_starts_with($actual, '"""')) {
            // https://github.com/webonyx/graphql-php/issues/1027
            Parser::schemaDefinition($actual);
        }

        self::assertSame($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::schemaDefinition(
            'schema @a @b { query: Query, mutation: Mutation }',
        );
        $block      = new SchemaDefinition($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['Query' => 'Query', 'Mutation' => 'Mutation'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new SchemaDefinition($context, Parser::schemaDefinition($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }

    public function testStatisticsTypeFilter(): void {
        $schema     = BuildSchema::build(
            <<<'GRAPHQL'
            type Query {
                field(a: Int): String
            }

            type Mutation {
                field(a: Int): String
            }
            GRAPHQL,
        );
        $settings   = (new TestSettings())->setTypeFilter(static function (string $type): bool {
            return $type !== 'Mutation';
        });
        $context    = new Context($settings, null, $schema);
        $collector  = new Collector();
        $definition = Parser::schemaDefinition(
            'schema { query: Query, mutation: Mutation }',
        );
        $block      = new SchemaDefinition($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertEmpty($content);
        self::assertEquals(['Query' => 'Query'], $collector->getUsedTypes());
        self::assertEquals([], $collector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, SchemaDefinitionNode|Schema, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(false)
            ->setNormalizeFields(false);

        return [
            'standard names'                      => [
                '',
                $settings,
                0,
                0,
                new Schema([
                    'query'        => new ObjectType(['name' => 'Query', 'fields' => []]),
                    'mutation'     => new ObjectType(['name' => 'Mutation', 'fields' => []]),
                    'subscription' => new ObjectType(['name' => 'Subscription', 'fields' => []]),
                ]),
                null,
            ],
            'standard names with directives'      => [
                <<<'GRAPHQL'
                schema
                @a
                @b
                @c
                {
                    query: Query
                    mutation: Mutation
                    subscription: Subscription
                }
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new Schema([
                    'query'             => new ObjectType(['name' => 'Query', 'fields' => []]),
                    'mutation'          => new ObjectType(['name' => 'Mutation', 'fields' => []]),
                    'subscription'      => new ObjectType(['name' => 'Subscription', 'fields' => []]),
                    'astNode'           => Parser::schemaDefinition(
                        <<<'GRAPHQL'
                        schema @a { query: Query }
                        GRAPHQL,
                    ),
                    'extensionASTNodes' => [
                        Parser::schemaTypeExtension('extend schema @b'),
                        Parser::schemaTypeExtension('extend schema @c'),
                    ],
                ]),
                null,
            ],
            'non standard names'                  => [
                <<<'GRAPHQL'
                schema {
                    query: MyQuery
                    mutation: Mutation
                    subscription: Subscription
                }
                GRAPHQL,
                $settings,
                0,
                0,
                new Schema([
                    'query'        => new ObjectType(['name' => 'MyQuery', 'fields' => []]),
                    'mutation'     => new ObjectType(['name' => 'Mutation', 'fields' => []]),
                    'subscription' => new ObjectType(['name' => 'Subscription', 'fields' => []]),
                ]),
                null,
            ],
            'indent'                              => [
                <<<'GRAPHQL'
                schema {
                        query: MyQuery
                        mutation: Mutation
                        subscription: Subscription
                    }
                GRAPHQL,
                $settings,
                1,
                0,
                new Schema([
                    'query'        => new ObjectType(['name' => 'MyQuery', 'fields' => []]),
                    'mutation'     => new ObjectType(['name' => 'Mutation', 'fields' => []]),
                    'subscription' => new ObjectType(['name' => 'Subscription', 'fields' => []]),
                ]),
                null,
            ],
            'filter (no schema)'                  => [
                <<<'GRAPHQL'
                schema {
                    query: MyQuery
                    mutation: Mutation
                }
                GRAPHQL,
                $settings
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Mutation';
                    }),
                0,
                0,
                new Schema([
                    'query'    => new ObjectType(['name' => 'MyQuery', 'fields' => []]),
                    'mutation' => new ObjectType(['name' => 'Mutation', 'fields' => []]),
                ]),
                null,
            ],
            'filter'                              => [
                <<<'GRAPHQL'
                schema {
                    query: MyQuery
                }
                GRAPHQL,
                $settings
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Mutation';
                    }),
                0,
                0,
                new Schema([
                    'query'    => new ObjectType(['name' => 'MyQuery', 'fields' => []]),
                    'mutation' => new ObjectType(['name' => 'Mutation', 'fields' => []]),
                ]),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type MyQuery {
                        a: Int
                    }
                    GRAPHQL,
                ),
            ],
            'ast: standard names'                 => [
                '',
                $settings,
                0,
                0,
                Parser::schemaDefinition(
                    <<<'GRAPHQL'
                    schema {
                        query: Query
                        mutation: Mutation
                        subscription: Subscription
                    }
                    GRAPHQL,
                ),
                null,
            ],
            'ast: standard names with directives' => [
                <<<'GRAPHQL'
                schema
                @a
                {
                    query: Query
                    mutation: Mutation
                    subscription: Subscription
                }
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::schemaDefinition(
                    <<<'GRAPHQL'
                    schema @a {
                        query: Query
                        mutation: Mutation
                        subscription: Subscription
                    }
                    GRAPHQL,
                ),
                null,
            ],
            'ast: non standard names'             => [
                <<<'GRAPHQL'
                schema {
                    query: MyQuery
                    mutation: Mutation
                    subscription: Subscription
                }
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::schemaDefinition(
                    <<<'GRAPHQL'
                    schema {
                        query: MyQuery
                        mutation: Mutation
                        subscription: Subscription
                    }
                    GRAPHQL,
                ),
                null,
            ],
            'ast: filter (no schema)'             => [
                <<<'GRAPHQL'
                schema
                @a
                {
                    query: MyQuery
                    mutation: Mutation
                }
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Mutation';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::schemaDefinition(
                    <<<'GRAPHQL'
                    schema @a @b {
                        query: MyQuery
                        mutation: Mutation
                    }
                    GRAPHQL,
                ),
                null,
            ],
            'ast: filter'                         => [
                <<<'GRAPHQL'
                schema
                @a
                {
                    query: MyQuery
                }
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Mutation';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::schemaDefinition(
                    <<<'GRAPHQL'
                    schema @a @b {
                        query: MyQuery
                        mutation: Mutation
                    }
                    GRAPHQL,
                ),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type MyQuery {
                        a: Int
                    }
                    GRAPHQL,
                ),
            ],
        ];
    }
    // </editor-fold>
}
