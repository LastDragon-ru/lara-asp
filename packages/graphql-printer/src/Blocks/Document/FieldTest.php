<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\Node;
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
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Field::class)]
final class FieldTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        FieldNode $definition,
        (TypeNode&Node)|Type|null $type,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new Field($context, $definition, $type))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::field($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $schema     = BuildSchema::build(
            <<<'GRAPHQL'
            type A {
                field(a: Int): B
            }

            type B {
                a: String
            }
            GRAPHQL,
        );
        $context    = new Context(new TestSettings(), null, $schema);
        $collector  = new Collector();
        $definition = Parser::field('alias: field(a: 123) @a { a }');
        $type       = $schema->getType('A');
        $block      = new Field($context, $definition, $type);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A', 'B' => 'B', 'String' => 'String', 'Int' => 'Int'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new Field($context, Parser::field($content), $type);

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }

    public function testStatisticsNoSchema(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::field('alias: field(a: 123) @a');
        $type       = null;
        $block      = new Field($context, $definition, $type);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals([], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new Field($context, Parser::field($content), $type);

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, FieldNode, (TypeNode&Node)|Type|null, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);

        return [
            'without args'               => [
                <<<'STRING'
                test
                @a
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::field(
                    'test @a',
                ),
                null,
                null,
            ],
            'with args (short)'          => [
                <<<'STRING'
                alias: test(a: ["aaaaaaaaaaaaaaaaaaaaaaaaaa"], b: Int)
                STRING,
                $settings,
                0,
                0,
                Parser::field(
                    'alias: test(a: ["aaaaaaaaaaaaaaaaaaaaaaaaaa"], b: Int)',
                ),
                null,
                null,
            ],
            'with args (long)'           => [
                <<<'STRING'
                alias: test(
                    a: [
                        "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                    ]
                    b: Int
                )
                STRING,
                $settings
                    ->setLineLength(36),
                0,
                120,
                Parser::field(
                    'alias: test(a: ["aaaaaaaaaaaaaaaaaaaaaaaaaa"], b: Int)',
                ),
                null,
                null,
            ],
            'with args normalized'       => [
                <<<'STRING'
                test(a: 123, b: 321)
                STRING,
                $settings->setNormalizeArguments(true),
                0,
                0,
                Parser::field(
                    'test(b: 321, a: 123)',
                ),
                null,
                null,
            ],
            'with args always multiline' => [
                <<<'STRING'
                test(
                    b: 321
                    a: 123
                )
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true),
                0,
                0,
                Parser::field(
                    'test(b: 321, a: 123)',
                ),
                null,
                null,
            ],
            'indent'                     => [
                <<<'STRING'
                alias: test(
                        a: 123
                        b: 321
                    )
                    @a
                    {
                        test
                    }
                STRING,
                $settings
                    ->setPrintDirectives(true)
                    ->setNormalizeArguments(true)
                    ->setAlwaysMultilineArguments(true),
                1,
                120,
                Parser::field(
                    'alias: test(b: 321, a: 123) @a { test }',
                ),
                null,
                null,
            ],
            'filter (no schema)'         => [
                <<<'STRING'
                test
                @a
                STRING,
                $settings
                    ->setTypeFilter(static fn () => false)
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::field(
                    'test @a @b',
                ),
                Type::int(),
                null,
            ],
            'filter'                     => [
                '',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::field(
                    'test',
                ),
                Parser::typeReference('A'),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type A {
                        test: String
                    }
                    GRAPHQL,
                ),
            ],
            'filter: type'               => [
                '',
                $settings
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Int';
                    }),
                0,
                0,
                Parser::field(
                    'test',
                ),
                Parser::typeReference('A'),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type A {
                        test: Int
                    }
                    GRAPHQL,
                ),
            ],
            'filter: arg'                => [
                'test(b: "321")',
                $settings
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Int';
                    }),
                0,
                0,
                Parser::field(
                    'test(a: 123, b: "321")',
                ),
                Parser::typeReference('A'),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type A {
                        test(a: Int, b: String): String
                    }
                    GRAPHQL,
                ),
            ],
        ];
    }
    // </editor-fold>
}
