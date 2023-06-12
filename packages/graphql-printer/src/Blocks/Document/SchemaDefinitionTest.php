<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
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
        Schema $schema,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new SchemaDefinition($context, $level, $used, $schema));

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
     * @return array<string,array{string, Settings, int, int, Schema}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(false)
            ->setNormalizeFields(false);

        return [
            'standard names'                 => [
                '',
                $settings,
                0,
                0,
                new Schema([
                    'query'        => new ObjectType(['name' => 'Query', 'fields' => []]),
                    'mutation'     => new ObjectType(['name' => 'Mutation', 'fields' => []]),
                    'subscription' => new ObjectType(['name' => 'Subscription', 'fields' => []]),
                ]),
            ],
            'standard names with directives' => [
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
            ],
            'non standard names'             => [
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
            ],
            'indent'                         => [
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
            ],
        ];
    }
    // </editor-fold>
}
