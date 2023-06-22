<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

use function str_starts_with;

/**
 * @internal
 */
#[CoversClass(SchemaDefinition::class)]
#[CoversClass(RootOperationTypeDefinition::class)]
#[CoversClass(RootOperationTypesDefinition::class)]
class SchemaDefinitionTest extends TestCase {
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
        SchemaDefinitionNode|Schema $definition,
        ?Schema $schema,
    ): void {
        $context = new Context($settings, null, $schema);
        $actual  = (string) (new SchemaDefinition($context, $level, $used, $definition));

        if ($expected && !str_starts_with($actual, '"""')) {
            // https://github.com/webonyx/graphql-php/issues/1027
            Parser::schemaDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, SchemaDefinitionNode|Schema, ?Schema}>
     */
    public static function dataProviderToString(): array {
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
                <<<'STRING'
                schema
                @a
                @b
                @c
                {
                    query: Query
                    mutation: Mutation
                    subscription: Subscription
                }
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new Schema([
                    'query'             => new ObjectType(['name' => 'Query', 'fields' => []]),
                    'mutation'          => new ObjectType(['name' => 'Mutation', 'fields' => []]),
                    'subscription'      => new ObjectType(['name' => 'Subscription', 'fields' => []]),
                    'astNode'           => Parser::schemaDefinition(
                        <<<'STRING'
                        schema @a { query: Query }
                        STRING,
                    ),
                    'extensionASTNodes' => [
                        Parser::schemaTypeExtension('extend schema @b'),
                        Parser::schemaTypeExtension('extend schema @c'),
                    ],
                ]),
                null,
            ],
            'non standard names'                  => [
                <<<'STRING'
                schema {
                    query: MyQuery
                    mutation: Mutation
                    subscription: Subscription
                }
                STRING,
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
                <<<'STRING'
                schema {
                        query: MyQuery
                        mutation: Mutation
                        subscription: Subscription
                    }
                STRING,
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
                <<<'STRING'
                schema {
                    query: MyQuery
                    mutation: Mutation
                }
                STRING,
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
                <<<'STRING'
                schema {
                    query: MyQuery
                }
                STRING,
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
                    <<<'STRING'
                    type MyQuery {
                        a: Int
                    }
                    STRING,
                ),
            ],
            'ast: standard names'                 => [
                '',
                $settings,
                0,
                0,
                Parser::schemaDefinition(
                    <<<'STRING'
                    schema {
                        query: Query
                        mutation: Mutation
                        subscription: Subscription
                    }
                    STRING,
                ),
                null,
            ],
            'ast: standard names with directives' => [
                <<<'STRING'
                schema
                @a
                {
                    query: Query
                    mutation: Mutation
                    subscription: Subscription
                }
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::schemaDefinition(
                    <<<'STRING'
                    schema @a {
                        query: Query
                        mutation: Mutation
                        subscription: Subscription
                    }
                    STRING,
                ),
                null,
            ],
            'ast: non standard names'             => [
                <<<'STRING'
                schema {
                    query: MyQuery
                    mutation: Mutation
                    subscription: Subscription
                }
                STRING,
                $settings,
                0,
                0,
                Parser::schemaDefinition(
                    <<<'STRING'
                    schema {
                        query: MyQuery
                        mutation: Mutation
                        subscription: Subscription
                    }
                    STRING,
                ),
                null,
            ],
            'ast: filter (no schema)'             => [
                <<<'STRING'
                schema
                @a
                {
                    query: MyQuery
                    mutation: Mutation
                }
                STRING,
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
                    <<<'STRING'
                    schema @a @b {
                        query: MyQuery
                        mutation: Mutation
                    }
                    STRING,
                ),
                null,
            ],
            'ast: filter'                         => [
                <<<'STRING'
                schema
                @a
                {
                    query: MyQuery
                }
                STRING,
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
                    <<<'STRING'
                    schema @a @b {
                        query: MyQuery
                        mutation: Mutation
                    }
                    STRING,
                ),
                BuildSchema::build(
                    <<<'STRING'
                    type MyQuery {
                        a: Int
                    }
                    STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
