<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(FragmentDefinition::class)]
final class FragmentDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        FragmentDefinitionNode $definition,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new FragmentDefinition($context, $definition))->serialize($collector, $level, $used);

        if ($expected !== '') {
            Parser::fragmentDefinition($actual);
        }

        self::assertSame($expected, $actual);
    }

    /**
     * @param array{types: array<string, string>, directives: array<string, string>} $expected
     */
    #[DataProvider('dataProviderStatistics')]
    public function testStatistics(
        array $expected,
        FragmentDefinitionNode $definition,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context(new TestSettings(), null, $schema);
        $block     = new FragmentDefinition($context, $definition);
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals($expected['types'], $collector->getUsedTypes());
        self::assertEquals($expected['directives'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new FragmentDefinition($context, Parser::fragmentDefinition($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      array{types: array<string, string>, directives: array<string, string>},
     *      FragmentDefinitionNode,
     *      ?Schema,
     *      }>
     */
    public static function dataProviderStatistics(): array {
        return [
            'schema'    => [
                [
                    'types'      => ['A' => 'A', 'Int' => 'Int'],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragmentDefinition('fragment Test on A @a { a }'),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type A {
                        a: Int
                    }

                    type B {
                        b: String
                    }
                    GRAPHQL,
                ),
            ],
            'no schema' => [
                [
                    'types'      => ['A' => 'A'],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragmentDefinition('fragment Test on A @a { a }'),
                null,
            ],
        ];
    }

    /**
     * @return array<string,array{
     *      string,
     *      Settings,
     *      int,
     *      int,
     *      FragmentDefinitionNode,
     *      ?Schema,
     *      }>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false)
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);

        return [
            'named'              => [
                <<<'GRAPHQL'
                fragment Test on A {
                    a
                    b
                }
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::fragmentDefinition(
                    'fragment Test on A { a, b }',
                ),
                null,
            ],
            'normalized'         => [
                <<<'GRAPHQL'
                fragment Test on A
                @b
                @a
                {
                    a

                    b(a: 123, b: "321") {
                        c
                    }
                }
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true)
                    ->setNormalizeFields(true)
                    ->setNormalizeArguments(true),
                0,
                0,
                Parser::fragmentDefinition(
                    'fragment Test on A @b @a { b(a: 123, b: "321") { c }, a }',
                ),
                null,
            ],
            'indent'             => [
                <<<'GRAPHQL'
                fragment Test on A {
                        a
                        b
                    }
                GRAPHQL,
                $settings,
                1,
                120,
                Parser::fragmentDefinition(
                    'fragment Test on A { a, b }',
                ),
                null,
            ],
            'filter (directive)' => [
                <<<'GRAPHQL'
                fragment Test on A
                @a
                {
                    a
                    b
                }
                GRAPHQL,
                $settings
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::fragmentDefinition(
                    'fragment Test on A @a @b { a, b }',
                ),
                null,
            ],
            'filter (no schema)' => [
                <<<'GRAPHQL'
                fragment Test on B {
                    b
                }
                GRAPHQL,
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragmentDefinition(
                    'fragment Test on B { b }',
                ),
                null,
            ],
            'filter (schema)'    => [
                '',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragmentDefinition(
                    'fragment Test on B { b }',
                ),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type B {
                        b: String
                    }
                    GRAPHQL,
                ),
            ],
            'filter'             => [
                <<<'GRAPHQL'
                fragment Test on B {
                    b(b: "321") {
                        c
                    }
                }
                GRAPHQL,
                $settings
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Int';
                    }),
                0,
                0,
                Parser::fragmentDefinition(
                    'fragment Test on B { b(a: 123, b: "321") { c }, a }',
                ),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type B {
                        a: Int
                        b(a: Int, b: String): C
                    }

                    type C {
                        c: String
                    }
                    GRAPHQL,
                ),
            ],
        ];
    }
    // </editor-fold>
}
