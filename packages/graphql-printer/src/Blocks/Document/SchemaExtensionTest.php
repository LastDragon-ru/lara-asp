<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\SchemaExtensionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
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
#[CoversClass(SchemaExtension::class)]
#[CoversClass(RootOperationTypeDefinition::class)]
#[CoversClass(RootOperationTypesDefinition::class)]
final class SchemaExtensionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        SchemaExtensionNode $node,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new SchemaExtension($context, $node))->serialize($collector, $level, $used);

        if ($expected !== '') {
            Parser::schemaTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::schemaTypeExtension(
            'extend schema @a @b { query: Query, mutation: Mutation }',
        );
        $block      = new SchemaExtension($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['Query' => 'Query', 'Mutation' => 'Mutation'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new SchemaExtension($context, Parser::schemaTypeExtension($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, SchemaExtensionNode, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(true)
            ->setNormalizeFields(false);

        return [
            'without fields'     => [
                <<<'GRAPHQL'
                extend schema
                @a
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::schemaTypeExtension(
                    'extend schema @a',
                ),
                null,
            ],
            'with fields'        => [
                <<<'GRAPHQL'
                extend schema
                @a
                {
                    query: Query
                }
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::schemaTypeExtension(
                    'extend schema @a { query: Query }',
                ),
                null,
            ],
            'indent'             => [
                <<<'GRAPHQL'
                extend schema {
                        query: Query
                    }
                GRAPHQL,
                $settings,
                1,
                0,
                Parser::schemaTypeExtension(
                    'extend schema { query: Query }',
                ),
                null,
            ],
            'filter (no schema)' => [
                <<<'GRAPHQL'
                extend schema
                @a
                {
                    query: Query
                    mutation: Mutation
                }
                GRAPHQL,
                $settings
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Mutation';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::schemaTypeExtension(
                    'extend schema @a @b { query: Query, mutation: Mutation }',
                ),
                null,
            ],
            'filter'             => [
                <<<'GRAPHQL'
                extend schema
                @a
                {
                    query: Query
                }
                GRAPHQL,
                $settings
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Mutation';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::schemaTypeExtension(
                    'extend schema @a @b { query: Query, mutation: Mutation }',
                ),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    scalar A
                    GRAPHQL,
                ),
            ],
        ];
    }
    // </editor-fold>
}
