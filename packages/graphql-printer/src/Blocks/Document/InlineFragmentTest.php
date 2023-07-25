<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
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

/**
 * @internal
 */
#[CoversClass(InlineFragment::class)]
class InlineFragmentTest extends TestCase {
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
        InlineFragmentNode $definition,
        TypeNode|Type|null $type,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new InlineFragment($context, $definition, $type))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::fragment($actual);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderStatistics
     *
     * @param array{types: array<string, string>, directives: array<string, string>} $expected
     * @param (TypeNode&Node)|Type|null                                              $type
     */
    public function testStatistics(
        array $expected,
        InlineFragmentNode $definition,
        TypeNode|Type|null $type,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context(new TestSettings(), null, $schema);
        $block     = new InlineFragment($context, $definition, $type);
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals($expected['types'], $collector->getUsedTypes());
        self::assertEquals($expected['directives'], $collector->getUsedDirectives());

        $astFragmentNode = Parser::fragment($content);

        self::assertInstanceOf(InlineFragmentNode::class, $astFragmentNode);

        $astCollector = new Collector();
        $astBlock     = new InlineFragment($context, $astFragmentNode, $type);

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
     *      InlineFragmentNode|FragmentSpreadNode,
     *      (TypeNode&Node)|Type|null,
     *      ?Schema,
     *      }>
     */
    public static function dataProviderStatistics(): array {
        return [
            'named + schema'     => [
                [
                    'types'      => ['A' => 'A', 'Int' => 'Int'],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragment('... on A @a { a }'),
                Parser::typeReference('B'),
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
            'anonymous + schema' => [
                [
                    'types'      => ['B' => 'B', 'String' => 'String'],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragment('... @a { b }'),
                Parser::typeReference('B'),
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
            'named'              => [
                [
                    'types'      => ['A' => 'A'],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragment('... on A @a { a }'),
                null,
                null,
            ],
            'anonymous'          => [
                [
                    'types'      => [],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragment('... @a { b }'),
                null,
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
     *      InlineFragmentNode|FragmentSpreadNode,
     *      (TypeNode&Node)|Type|null,
     *      ?Schema,
     *      }>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false)
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);

        return [
            'named'                       => [
                <<<'STRING'
                ... on A {
                    a
                    b
                }
                STRING,
                $settings,
                0,
                0,
                Parser::fragment(
                    '... on A { a, b }',
                ),
                null,
                null,
            ],
            'anonymous'                   => [
                <<<'STRING'
                ... {
                    a
                    b
                }
                STRING,
                $settings,
                0,
                0,
                Parser::fragment(
                    '... { a, b }',
                ),
                null,
                null,
            ],
            'with args'                   => [
                <<<'STRING'
                ... on A
                @b
                @a
                {
                    b(a: 123, b: "321")
                    @b
                    @a
                    {
                        c
                    }

                    a
                }
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::fragment(
                    '... on A @b @a { b(a: 123, b: "321") @b @a { c }, a }',
                ),
                null,
                null,
            ],
            'with args always multiline'  => [
                <<<'STRING'
                ... on A {
                    b(
                        a: 123
                        b: "321"
                    ) {
                        c
                    }

                    a
                }
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true),
                0,
                0,
                Parser::fragment(
                    '... on A { b(a: 123, b: "321") { c }, a }',
                ),
                null,
                null,
            ],
            'normalized'                  => [
                <<<'STRING'
                ... on A
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
                Parser::fragment(
                    '... on A @b @a { b(a: 123, b: "321") { c }, a }',
                ),
                null,
                null,
            ],
            'indent'                      => [
                <<<'STRING'
                ... {
                        a
                        b
                    }
                STRING,
                $settings,
                1,
                120,
                Parser::fragment(
                    '... { a, b }',
                ),
                null,
                null,
            ],
            'filter (directive)'          => [
                <<<'STRING'
                ...
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
                Parser::fragment(
                    '... @a @b { a, b }',
                ),
                null,
                null,
            ],
            'filter (no schema, no type)' => [
                <<<'STRING'
                ... on B {
                    b
                }
                STRING,
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragment(
                    '... on B { b }',
                ),
                null,
                null,
            ],
            'filter (schema, no type)'    => [
                '',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragment(
                    '... on B { b }',
                ),
                null,
                BuildSchema::build(
                    <<<'STRING'
                    type B {
                        b: String
                    }
                    STRING,
                ),
            ],
            'filter (no schema, type)'    => [
                <<<'STRING'
                ... on B {
                    b
                }
                STRING,
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragment(
                    '... on B { b }',
                ),
                Parser::typeReference('B'),
                null,
            ],
            'filter'                      => [
                <<<'STRING'
                ... on B {
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
                Parser::fragment(
                    '... on B { b(a: 123, b: "321") { c }, a }',
                ),
                Parser::typeReference('A'),
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
            'filter anonymous'            => [
                <<<'STRING'
                ... {
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
                Parser::fragment(
                    '... { b(a: 123, b: "321") { c }, a }',
                ),
                Parser::typeReference('B'),
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
