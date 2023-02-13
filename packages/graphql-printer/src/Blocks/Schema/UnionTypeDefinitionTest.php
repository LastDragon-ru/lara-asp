<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\UnionTypeDefinition
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\UnionMemberTypes
 */
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
        UnionType $type,
    ): void {
        $actual = (string) (new UnionTypeDefinition($settings, $level, $used, $type));

        Parser::unionTypeDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $union    = new UnionType([
            'name'    => 'Test',
            'types'   => [
                new ObjectType([
                    'name' => 'A',
                ]),
                new ObjectType([
                    'name' => 'B',
                ]),
            ],
            'astNode' => Parser::unionTypeDefinition('union Test @a = A | B'),
        ]);
        $settings = new TestSettings();
        $block    = new UnionTypeDefinition($settings, 0, 0, $union);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['A' => 'A', 'B' => 'B'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, UnionType}>
     */
    public function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeUnions(false)
            ->setAlwaysMultilineUnions(false);

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
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
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
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
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
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
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
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
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
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
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
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
                    ],
                ]),
            ],
            'directives'                    => [
                <<<'STRING'
                union Test
                @a
                = C | B | A
                STRING,
                $settings,
                0,
                0,
                new UnionType([
                    'name'    => 'Test',
                    'types'   => [
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
                    ],
                    'astNode' => Parser::unionTypeDefinition(
                        <<<'STRING'
                        union Test @a = A | B | C
                        STRING,
                    ),
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
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
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
                        new ObjectType([
                            'name' => 'A',
                        ]),
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
