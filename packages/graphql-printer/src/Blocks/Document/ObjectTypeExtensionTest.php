<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ObjectTypeExtension::class)]
class ObjectTypeExtensionTest extends TestCase {
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
        ObjectTypeExtensionNode $definition,
        ?Schema $schema,
    ): void {
        $context = new Context($settings, null, $schema);
        $actual  = (new ObjectTypeExtension($context, $level, $used, $definition))->serialize($level, $used);

        if ($expected) {
            Parser::objectTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = Parser::objectTypeExtension(
            <<<'STRING'
            extend type Test implements B & A @a {
                a: String
            }
            STRING,
        );
        $block      = new ObjectTypeExtension($context, 0, 0, $definition);
        $content    = $block->serialize(0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['B' => 'B', 'A' => 'A', 'String' => 'String'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $block->getUsedDirectives());

        $ast = new ObjectTypeExtension($context, 0, 0, Parser::objectTypeExtension($content));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ObjectTypeExtensionNode, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false)
            ->setNormalizeInterfaces(false)
            ->setAlwaysMultilineArguments(false)
            ->setAlwaysMultilineInterfaces(false);

        return [
            'directives'                                  => [
                <<<'STRING'
                extend type Test
                @a
                @b
                @c
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::objectTypeExtension(
                    'extend type Test @a @b @c',
                ),
                null,
            ],
            'fields'                                      => [
                <<<'STRING'
                extend type Test {
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
                Parser::objectTypeExtension(
                    <<<'STRING'
                    extend type Test {
                        b: B

                        "Description"
                        a(a: Int): A
                    }
                    STRING,
                ),
                null,
            ],
            'implements'                                  => [
                <<<'STRING'
                extend type Test implements A & B
                STRING,
                $settings
                    ->setNormalizeInterfaces(true),
                0,
                0,
                Parser::objectTypeExtension(
                    'extend type Test implements B & A',
                ),
                null,
            ],
            'implements(multiline) + directives + fields' => [
                <<<'STRING'
                extend type Test
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
                Parser::objectTypeExtension(
                    <<<'STRING'
                    extend type Test implements B & A @a {
                        a: String
                    }
                    STRING,
                ),
                null,
            ],
            'indent'                                      => [
                <<<'STRING'
                extend type Test implements B & A
                    @a
                    {
                        a: String
                    }
                STRING,
                $settings
                    ->setPrintDirectives(true),
                1,
                0,
                Parser::objectTypeExtension(
                    <<<'STRING'
                    extend type Test implements B & A @a {
                        a: String
                    }
                    STRING,
                ),
                null,
            ],
            'implements always multiline'                 => [
                <<<'STRING'
                extend type Test
                implements
                    & B
                STRING,
                $settings
                    ->setAlwaysMultilineInterfaces(true),
                0,
                0,
                Parser::objectTypeExtension(
                    'extend type Test implements B',
                ),
                null,
            ],
            'filter: definition'                          => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::objectTypeExtension(
                    'extend type Test implements B',
                ),
                null,
            ],
            'filter: no schema'                           => [
                <<<'STRING'
                extend type Test implements B & A
                @a
                {
                    a(b: B): A
                    b: [B!]
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
                Parser::objectTypeExtension(
                    <<<'STRING'
                    extend type Test implements B & A @a @b {
                        a(b: B): A
                        b: [B!]
                    }
                    STRING,
                ),
                null,
            ],
            'filter'                                      => [
                <<<'STRING'
                extend type Test implements A
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
                Parser::objectTypeExtension(
                    <<<'STRING'
                    extend type Test implements B & A @a @b {
                        a(b: B): A
                        b: [B!]
                    }
                    STRING,
                ),
                BuildSchema::build(
                    <<<'STRING'
                    scalar A
                    scalar B
                    STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
