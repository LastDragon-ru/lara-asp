<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\SchemaExtensionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SchemaExtension::class)]
#[CoversClass(RootOperationTypeDefinition::class)]
#[CoversClass(RootOperationTypesDefinition::class)]
class SchemaExtensionTest extends TestCase {
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
        SchemaExtensionNode $schema,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new SchemaExtension($context, $level, $used, $schema));

        if ($expected) {
            Parser::schemaTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = Parser::schemaTypeExtension(
            'extend schema @a @b { query: Query, mutation: Mutation }',
        );
        $block      = new SchemaExtension($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['Query' => 'Query', 'Mutation' => 'Mutation'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $block->getUsedDirectives());

        $ast = new SchemaExtension($context, 0, 0, Parser::schemaTypeExtension((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, SchemaExtensionNode}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(true)
            ->setNormalizeFields(false);

        return [
            'without fields' => [
                <<<'STRING'
                extend schema
                @a
                STRING,
                $settings,
                0,
                0,
                Parser::schemaTypeExtension(
                    'extend schema @a',
                ),
            ],
            'with fields'    => [
                <<<'STRING'
                extend schema
                @a
                {
                    query: Query
                }
                STRING,
                $settings,
                0,
                0,
                Parser::schemaTypeExtension(
                    'extend schema @a { query: Query }',
                ),
            ],
            'indent'         => [
                <<<'STRING'
                extend schema {
                        query: Query
                    }
                STRING,
                $settings,
                1,
                0,
                Parser::schemaTypeExtension(
                    'extend schema { query: Query }',
                ),
            ],
            'filter'         => [
                <<<'STRING'
                extend schema
                @a
                {
                    query: Query
                }
                STRING,
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
            ],
        ];
    }
    // </editor-fold>
}
