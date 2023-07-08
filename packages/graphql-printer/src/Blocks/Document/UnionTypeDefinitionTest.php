<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(UnionTypeDefinition::class)]
#[CoversClass(UnionMemberTypes::class)]
class UnionTypeDefinitionTest extends TestCase {
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
        UnionTypeDefinitionNode|UnionType $type,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new UnionTypeDefinition($context, $type))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::unionTypeDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $union     = new UnionType([
            'name'    => 'Test',
            'types'   => [
                new ObjectType([
                    'name'   => 'A',
                    'fields' => [
                        'field' => [
                            'type' => GraphQLType::string(),
                        ],
                    ],
                ]),
                new ObjectType([
                    'name'   => 'B',
                    'fields' => [
                        'field' => [
                            'type' => GraphQLType::string(),
                        ],
                    ],
                ]),
            ],
            'astNode' => Parser::unionTypeDefinition('union Test @a = A | B'),
        ]);
        $context   = new Context(new TestSettings(), null, null);
        $collector = new Collector();
        $block     = new UnionTypeDefinition($context, $union);
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['Test' => 'Test', 'A' => 'A', 'B' => 'B'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new UnionTypeDefinition($context, Parser::unionTypeDefinition($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, UnionTypeDefinitionNode|UnionType}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeUnions(false)
            ->setAlwaysMultilineUnions(false);
        $a        = new ObjectType([
            'name'   => 'A',
            'fields' => [
                'field' => [
                    'type' => GraphQLType::string(),
                ],
            ],
        ]);
        $b        = new ObjectType([
            'name'   => 'B',
            'fields' => [
                'field' => [
                    'type' => GraphQLType::string(),
                ],
            ],
        ]);
        $c        = new ObjectType([
            'name'   => 'C',
            'fields' => [
                'field' => [
                    'type' => GraphQLType::string(),
                ],
            ],
        ]);

        return [
            'single-line'                   => [
                <<<'STRING'
                union Test = C | B | A
                STRING,
                $settings,
                0,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        $c,
                        $b,
                        $a,
                    ],
                ]),
            ],
            'multiline'                     => [
                <<<'STRING'
                union Test =
                    | C
                    | B
                    | A
                STRING,
                $settings,
                0,
                120,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        $c,
                        $b,
                        $a,
                    ],
                ]),
            ],
            'indent single-line'            => [
                <<<'STRING'
                union Test = C | B | A
                STRING,
                $settings,
                1,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        $c,
                        $b,
                        $a,
                    ],
                ]),
            ],
            'indent multiline'              => [
                <<<'STRING'
                union Test =
                        | C
                        | B
                        | A
                STRING,
                $settings,
                1,
                120,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        $c,
                        $b,
                        $a,
                    ],
                ]),
            ],
            'multiline normalized'          => [
                <<<'STRING'
                union Test = A | B | C
                STRING,
                $settings->setNormalizeUnions(true),
                0,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        $c,
                        $b,
                        $a,
                    ],
                ]),
            ],
            'multiline always'              => [
                <<<'STRING'
                union Test =
                    | C
                    | B
                    | A
                STRING,
                $settings->setAlwaysMultilineUnions(true),
                0,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        $c,
                        $b,
                        $a,
                    ],
                ]),
            ],
            'directives'                    => [
                <<<'STRING'
                union Test
                @a
                @b
                @c
                = C | B | A
                STRING,
                $settings,
                0,
                0,
                new UnionType([
                    'name'              => 'Test',
                    'types'             => [
                        $c,
                        $b,
                        $a,
                    ],
                    'astNode'           => Parser::unionTypeDefinition(
                        <<<'STRING'
                        union Test @a = A | B | C
                        STRING,
                    ),
                    'extensionASTNodes' => [
                        Parser::unionTypeExtension('extend union Test @b'),
                        Parser::unionTypeExtension('extend union Test @c'),
                    ],
                ]),
            ],
            'directives + multiline'        => [
                <<<'STRING'
                union Test
                @a
                =
                    | C
                    | B
                    | A
                STRING,
                $settings
                    ->setLineLength(10),
                0,
                120,
                new UnionType([
                    'name'    => 'Test',
                    'types'   => [
                        $c,
                        $b,
                        $a,
                    ],
                    'astNode' => Parser::unionTypeDefinition(
                        <<<'STRING'
                        union Test @a = A | B | C
                        STRING,
                    ),
                ]),
            ],
            'one member + always multiline' => [
                <<<'STRING'
                union Test =
                    | A
                STRING,
                $settings->setAlwaysMultilineUnions(true),
                0,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        $a,
                    ],
                ]),
            ],
            'filter'                        => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        $c,
                    ],
                ]),
            ],
            'ast'                           => [
                <<<'STRING'
                """
                Description
                """
                union Test
                @a
                = C | B | A
                STRING,
                $settings
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::unionTypeDefinition(
                    '"Description" union Test @a @b = C | B | A',
                ),
            ],
            'ast + filter'                  => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::unionTypeDefinition(
                    'union Test = C | B | A',
                ),
            ],
        ];
    }
    // </editor-fold>
}
