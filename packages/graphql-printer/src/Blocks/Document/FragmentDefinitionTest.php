<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\Parser;
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
#[CoversClass(FragmentDefinition::class)]
class FragmentDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSerialize
     */
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

        if ($expected) {
            Parser::fragmentDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderStatistics
     *
     * @param array{types: array<string, string>, directives: array<string, string>} $expected
     */
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
                    <<<'STRING'
                    type A {
                        a: Int
                    }

                    type B {
                        b: String
                    }
                    STRING,
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
                <<<'STRING'
                fragment Test on A {
                    a
                    b
                }
                STRING,
                $settings,
                0,
                0,
                Parser::fragmentDefinition(
                    'fragment Test on A { a, b }',
                ),
                null,
            ],
            'normalized'         => [
                <<<'STRING'
                fragment Test on A
                @b
                @a
                {
                    a

                    b(a: 123, b: "321") {
                        c
                    }
                }
                STRING,
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
                <<<'STRING'
                fragment Test on A {
                        a
                        b
                    }
                STRING,
                $settings,
                1,
                120,
                Parser::fragmentDefinition(
                    'fragment Test on A { a, b }',
                ),
                null,
            ],
            'filter (directive)' => [
                <<<'STRING'
                fragment Test on A
                @a
                {
                    a
                    b
                }
                STRING,
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
                <<<'STRING'
                fragment Test on B {
                    b
                }
                STRING,
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
                    <<<'STRING'
                    type B {
                        b: String
                    }
                    STRING,
                ),
            ],
            'filter'             => [
                <<<'STRING'
                fragment Test on B {
                    b(b: "321") {
                        c
                    }
                }
                STRING,
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
                    <<<'STRING'
                    type B {
                        a: Int
                        b(a: Int, b: String): C
                    }

                    type C {
                        c: String
                    }
                    STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
