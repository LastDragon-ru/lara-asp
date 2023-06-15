<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(InterfaceTypeExtension::class)]
class InterfaceTypeExtensionTest extends TestCase {
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
        InterfaceTypeExtensionNode $definition,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new InterfaceTypeExtension($context, $level, $used, $definition));

        if ($expected) {
            Parser::interfaceTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = Parser::interfaceTypeExtension(
            <<<'STRING'
            extend interface Test implements B & A @a {
                a: String
            }
            STRING,
        );
        $block      = new InterfaceTypeExtension($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['B' => 'B', 'A' => 'A', 'String' => 'String'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $block->getUsedDirectives());

        $ast = new InterfaceTypeExtension($context, 0, 0, Parser::interfaceTypeExtension((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InterfaceTypeExtensionNode}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false)
            ->setNormalizeInterfaces(false)
            ->setAlwaysMultilineArguments(false)
            ->setAlwaysMultilineInterfaces(false);

        return [
            'directives'                                  => [
                <<<'STRING'
                extend interface Test
                @a
                @b
                @c
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::interfaceTypeExtension(
                    'extend interface Test @a @b @c',
                ),
            ],
            'fields'                                      => [
                <<<'STRING'
                extend interface Test {
                    """
                    Description
                    """
                    a(a: Int): A

                    b: B
                }
                STRING,
                $settings
                    ->setNormalizeFields(true),
                0,
                0,
                Parser::interfaceTypeExtension(
                    <<<'STRING'
                    extend interface Test {
                        b: B

                        "Description"
                        a(a: Int): A
                    }
                    STRING,
                ),
            ],
            'implements'                                  => [
                <<<'STRING'
                extend interface Test implements A & B
                STRING,
                $settings
                    ->setNormalizeInterfaces(true),
                0,
                0,
                Parser::interfaceTypeExtension(
                    'extend interface Test implements B & A',
                ),
            ],
            'implements(multiline) + directives + fields' => [
                <<<'STRING'
                extend interface Test
                implements
                    & B
                    & A
                @a
                {
                    a: String
                }
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                120,
                Parser::interfaceTypeExtension(
                    <<<'STRING'
                    extend interface Test implements B & A @a {
                        a: String
                    }
                    STRING,
                ),
            ],
            'indent'                                      => [
                <<<'STRING'
                extend interface Test implements B & A
                    @a
                    {
                        a: String
                    }
                STRING,
                $settings
                    ->setPrintDirectives(true),
                1,
                0,
                Parser::interfaceTypeExtension(
                    <<<'STRING'
                    extend interface Test implements B & A @a {
                        a: String
                    }
                    STRING,
                ),
            ],
            'implements always multiline'                 => [
                <<<'STRING'
                extend interface Test
                implements
                    & B
                STRING,
                $settings
                    ->setAlwaysMultilineInterfaces(true),
                0,
                0,
                Parser::interfaceTypeExtension(
                    'extend interface Test implements B',
                ),
            ],
            'filter: definition'                          => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::interfaceTypeExtension(
                    'extend interface Test implements B',
                ),
            ],
            'filter'                                      => [
                <<<'STRING'
                extend interface Test implements A
                @a
                {
                    a: A
                }
                STRING,
                $settings
                    ->setTypeFilter(static function (string $type): bool {
                        return $type !== 'B';
                    })
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::interfaceTypeExtension(
                    <<<'STRING'
                    extend interface Test implements B & A @a @b {
                        a: A
                        b: [B!]
                    }
                    STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
