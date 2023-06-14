<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
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
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        UnionTypeDefinitionNode|UnionType $type,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new UnionTypeDefinition($context, $level, $used, $type));

        if ($expected) {
            Parser::unionTypeDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $union   = new UnionType([
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
        $context = new Context(new TestSettings(), null, null);
        $block   = new UnionTypeDefinition($context, 0, 0, $union);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['A' => 'A', 'B' => 'B'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $block->getUsedDirectives());

        $ast = new UnionTypeDefinition($context, 0, 0, Parser::unionTypeDefinition((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, UnionTypeDefinitionNode|UnionType}>
     */
    public static function dataProviderToString(): array {
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
                $settings,
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
                $settings,
                0,
                0,
                Parser::unionTypeDefinition(
                    '"Description" union Test @a = C | B | A',
                ),
            ],
        ];
    }
    // </editor-fold>
}
