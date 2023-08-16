<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DocumentNode;
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
#[CoversClass(Document::class)]
class DocumentTest extends TestCase {
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
        DocumentNode $document,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new Document($context, $document))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::parse($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::parse(
            <<<'GRAPHQL'
            type Query {
                test: [Test]! @b
            }

            type Test {
                c: C! @c
            }

            extend type Test implements B & A @a {
                a: String
            }
            GRAPHQL,
        );
        $block      = new Document($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(
            [
                'String' => 'String',
                'Query'  => 'Query',
                'Test'   => 'Test',
                'A'      => 'A',
                'B'      => 'B',
                'C'      => 'C',
            ],
            $collector->getUsedTypes(),
        );
        self::assertEquals(
            [
                '@a' => '@a',
                '@b' => '@b',
                '@c' => '@c',
            ],
            $collector->getUsedDirectives(),
        );

        $astCollector = new Collector();
        $astBlock     = new Document($context, Parser::parse($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, DocumentNode, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(false)
            ->setNormalizeSchema(false)
            ->setNormalizeInterfaces(false)
            ->setNormalizeFields(false)
            ->setNormalizeEnums(false)
            ->setNormalizeUnions(false)
            ->setNormalizeArguments(false);
        $document = Parser::parse(
            <<<'GRAPHQL'
            type Query {
                test: [Test]! @b
            }

            type Test {
                c: C! @c
                b: B
                d: Int
            }

            extend type Test implements B & A @a {
                a: String
            }

            enum C {
                B
                C
                A
            }

            interface A {
                a: String
            }

            interface B {
                b: [B!]!
            }

            directive @a on FIELD | FIELD_DEFINITION | OBJECT | INTERFACE
            directive @b on FIELD | FIELD_DEFINITION | OBJECT | INTERFACE
            directive @c on FIELD | FIELD_DEFINITION | OBJECT | INTERFACE
            GRAPHQL,
        );

        return [
            'schema / default'               => [
                <<<'GRAPHQL'
                type Query {
                    test: [Test]!
                }

                type Test {
                    c: C!
                    b: B
                    d: Int
                }

                extend type Test
                implements
                    & B
                    & A
                {
                    a: String
                }

                enum C {
                    B
                    C
                    A
                }

                interface A {
                    a: String
                }

                interface B {
                    b: [B!]!
                }

                directive @a
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT

                directive @b
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT

                directive @c
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT
                GRAPHQL,
                $settings,
                0,
                0,
                $document,
                null,
            ],
            'schema / normalized'            => [
                <<<'GRAPHQL'
                directive @a
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT

                directive @b
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT

                directive @c
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT

                enum C {
                    A
                    B
                    C
                }

                extend type Test
                implements
                    & A
                    & B
                @a
                {
                    a: String
                }

                interface A {
                    a: String
                }

                interface B {
                    b: [B!]!
                }

                type Query {
                    test: [Test]!
                    @b
                }

                type Test {
                    b: B

                    c: C!
                    @c

                    d: Int
                }
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true)
                    ->setNormalizeSchema(true)
                    ->setNormalizeInterfaces(true)
                    ->setNormalizeFields(true)
                    ->setNormalizeEnums(true)
                    ->setNormalizeUnions(true)
                    ->setNormalizeArguments(true),
                0,
                0,
                $document,
                null,
            ],
            'schema / indent'                => [
                <<<'GRAPHQL'
                type Query {
                        test: [Test]!
                    }

                    type Test {
                        c: C!
                        b: B
                        d: Int
                    }

                    extend type Test
                    implements
                        & B
                        & A
                    {
                        a: String
                    }

                    enum C {
                        B
                        C
                        A
                    }

                    interface A {
                        a: String
                    }

                    interface B {
                        b: [B!]!
                    }

                    directive @a
                    on
                        | FIELD
                        | FIELD_DEFINITION
                        | INTERFACE
                        | OBJECT

                    directive @b
                    on
                        | FIELD
                        | FIELD_DEFINITION
                        | INTERFACE
                        | OBJECT

                    directive @c
                    on
                        | FIELD
                        | FIELD_DEFINITION
                        | INTERFACE
                        | OBJECT
                GRAPHQL,
                $settings,
                1,
                0,
                $document,
                null,
            ],
            'schema / filter (no schema)'    => [
                <<<'GRAPHQL'
                type Query {
                    test: [Test]!
                }

                type Test {
                    c: C!
                    @c

                    b: B
                    d: Int
                }

                extend type Test
                implements
                    & B
                    & A
                @a
                {
                    a: String
                }

                enum C {
                    B
                    C
                    A
                }

                interface A {
                    a: String
                }

                interface B {
                    b: [B!]!
                }

                directive @a
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT

                directive @c
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'B';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                $document,
                null,
            ],
            'schema / filter'                => [
                <<<'GRAPHQL'
                type Query {
                    test: [Test]!
                }

                type Test {
                    c: C!
                    @c

                    d: Int
                }

                extend type Test
                implements
                    & A
                @a
                {
                    a: String
                }

                enum C {
                    B
                    C
                    A
                }

                interface A {
                    a: String
                }

                directive @a
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT

                directive @c
                on
                    | FIELD
                    | FIELD_DEFINITION
                    | INTERFACE
                    | OBJECT
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'B';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                $document,
                BuildSchema::build(
                    <<<'GRAPHQL'
                    scalar B
                    GRAPHQL,
                ),
            ],
            'operation / default'            => [
                <<<'GRAPHQL'
                query test($a: Int) {
                    test {
                        b
                        c
                    }
                }
                GRAPHQL,
                $settings
                    ->setAlwaysMultilineArguments(false),
                0,
                0,
                Parser::parse(
                    'query test($a: Int) @b @a { test { b @b @a, c} }',
                ),
                BuildSchema::build($document),
            ],
            'operation / normalized'         => [
                <<<'GRAPHQL'
                query test($a: Int, $b: String)
                @b
                @a
                {
                    test {
                        b
                        @b
                        @a

                        c
                    }
                }
                GRAPHQL,
                $settings
                    ->setAlwaysMultilineArguments(false)
                    ->setPrintDirectives(true)
                    ->setNormalizeFields(true)
                    ->setNormalizeArguments(true),
                0,
                0,
                Parser::parse(
                    'query test($b: String, $a: Int) @b @a { test { c, b @b @a} }',
                ),
                BuildSchema::build($document),
            ],
            'operation / indent'             => [
                <<<'GRAPHQL'
                query test($a: Int, $b: String) {
                        test {
                            b
                            c
                        }
                    }
                GRAPHQL,
                $settings
                    ->setAlwaysMultilineArguments(false)
                    ->setNormalizeFields(true)
                    ->setNormalizeArguments(true),
                1,
                0,
                Parser::parse(
                    'query test($b: String, $a: Int) @b @a { test { c, b @b @a} }',
                ),
                BuildSchema::build($document),
            ],
            'operation / filter (no schema)' => [
                <<<'GRAPHQL'
                query test($b: String, $a: Int)
                @a
                {
                    test {
                        c

                        b
                        @a
                    }
                }
                GRAPHQL,
                $settings
                    ->setAlwaysMultilineArguments(false)
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Int';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::parse(
                    'query test($b: String, $a: Int) @b @a { test { c, b @b @a } }',
                ),
                null,
            ],
            'operation / filter'             => [
                <<<'GRAPHQL'
                query test($b: String)
                @a
                {
                    test {
                        c

                        b
                        @a
                    }
                }
                GRAPHQL,
                $settings
                    ->setAlwaysMultilineArguments(false)
                    ->setPrintDirectives(true)
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'Int';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::parse(
                    'query test($b: String, $a: Int) @b @a { test { c, b @b @a} }',
                ),
                BuildSchema::build($document),
            ],
        ];
    }
    // </editor-fold>
}
