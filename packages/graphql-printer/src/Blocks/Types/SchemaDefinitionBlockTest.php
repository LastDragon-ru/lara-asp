<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

use function str_starts_with;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\SchemaDefinitionBlock
 */
class SchemaDefinitionBlockTest extends TestCase {
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
        $actual = (string) (new SchemaDefinitionBlock($settings, $level, $used, $schema));

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
    public function dataProviderToString(): array {
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
                    'query'        => new ObjectType(['name' => 'Query']),
                    'mutation'     => new ObjectType(['name' => 'Mutation']),
                    'subscription' => new ObjectType(['name' => 'Subscription']),
                ]),
            ],
            'standard names with directives' => [
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
                new Schema([
                    'query'        => new ObjectType(['name' => 'Query']),
                    'mutation'     => new ObjectType(['name' => 'Mutation']),
                    'subscription' => new ObjectType(['name' => 'Subscription']),
                    'astNode'      => Parser::schemaDefinition(
                        <<<'STRING'
                        schema @a { query: Query }
                        STRING,
                    ),
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
                    'query'        => new ObjectType(['name' => 'MyQuery']),
                    'mutation'     => new ObjectType(['name' => 'Mutation']),
                    'subscription' => new ObjectType(['name' => 'Subscription']),
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
                    'query'        => new ObjectType(['name' => 'MyQuery']),
                    'mutation'     => new ObjectType(['name' => 'Mutation']),
                    'subscription' => new ObjectType(['name' => 'Subscription']),
                ]),
            ],
        ];
    }
    // </editor-fold>
}
