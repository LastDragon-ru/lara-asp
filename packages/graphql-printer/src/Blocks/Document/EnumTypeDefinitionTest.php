<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(EnumTypeDefinition::class)]
#[CoversClass(EnumValuesDefinition::class)]
class EnumTypeDefinitionTest extends TestCase {
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
        EnumTypeDefinitionNode|EnumType $type,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new EnumTypeDefinition($context, $level, $used, $type));

        if ($expected) {
            Parser::enumTypeDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = new EnumType([
            'name'    => 'Test',
            'values'  => ['A'],
            'astNode' => Parser::enumTypeDefinition(
                'enum Test @a { A }',
            ),
        ]);
        $block      = new EnumTypeDefinition($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals([], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $block->getUsedDirectives());

        $ast = new EnumTypeDefinition($context, 0, 0, Parser::enumTypeDefinition((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, EnumTypeDefinitionNode|EnumType}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeEnums(false);

        return [
            'enum'       => [
                <<<'STRING'
                enum Test {
                    C
                    B
                    A
                }
                STRING,
                $settings,
                0,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'indent'     => [
                <<<'STRING'
                enum Test {
                        C
                        B
                        A
                    }
                STRING,
                $settings,
                1,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'normalized' => [
                <<<'STRING'
                enum Test {
                    A
                    B
                    C
                }
                STRING,
                $settings->setNormalizeEnums(true),
                0,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'directives' => [
                <<<'STRING'
                enum Test
                @a
                @b
                @c
                {
                    A
                }
                STRING,
                $settings,
                0,
                0,
                new EnumType([
                    'name'              => 'Test',
                    'values'            => ['A'],
                    'astNode'           => Parser::enumTypeDefinition(
                        <<<'STRING'
                        enum Test @a { A }
                        STRING,
                    ),
                    'extensionASTNodes' => [
                        Parser::enumTypeExtension('extend enum Test @b'),
                        Parser::enumTypeExtension('extend enum Test @c'),
                    ],
                ]),
            ],
            'filter'     => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['A'],
                ]),
            ],
            'ast'        => [
                <<<'STRING'
                enum Test
                @a
                {
                    A
                }
                STRING,
                $settings,
                0,
                0,
                Parser::enumTypeDefinition(
                    'enum Test @a { A }',
                ),
            ],
        ];
    }
    // </editor-fold>
}
