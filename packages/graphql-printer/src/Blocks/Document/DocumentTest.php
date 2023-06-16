<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Document::class)]
class DocumentTest extends TestCase {
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
        DocumentNode $document,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new Document($context, $level, $used, $document));

        if ($expected) {
            Parser::parse($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = Parser::parse(
            <<<'STRING'
            type Query {
                test: [Test]! @b
            }

            type Test {
                c: C! @c
            }

            extend type Test implements B & A @a {
                a: String
            }
            STRING,
        );
        $block      = new Document($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(
            [
                'String' => 'String',
                'Query'  => 'Query',
                'Test'   => 'Test',
                'A'      => 'A',
                'B'      => 'B',
                'C'      => 'C',
            ],
            $block->getUsedTypes(),
        );
        self::assertEquals(
            [
                '@a' => '@a',
                '@b' => '@b',
                '@c' => '@c',
            ],
            $block->getUsedDirectives(),
        );

        $ast = new Document($context, 0, 0, Parser::parse((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, DocumentNode}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(false)
            ->setNormalizeSchema(false)
            ->setNormalizeInterfaces(false)
            ->setNormalizeFields(false)
            ->setNormalizeEnums(false)
            ->setNormalizeUnions(false)
            ->setNormalizeArguments(false);
        $document = Parser::parse(
            <<<'STRING'
            type Query {
                test: [Test]! @b
            }

            type Test {
                c: C! @c
                b: B
            }

            extend type Test implements B & A @a {
                a: String
                b: [B!]!
            }

            enum C {
                B
                C
                A
            }
            STRING,
        );

        return [
            'default'    => [
                <<<'STRING'
                type Query {
                    test: [Test]!
                }

                type Test {
                    c: C!
                    b: B
                }

                extend type Test
                implements
                    & B
                    & A
                {
                    a: String
                    b: [B!]!
                }

                enum C {
                    B
                    C
                    A
                }
                STRING,
                $settings,
                0,
                0,
                $document,
            ],
            'normalized' => [
                <<<'STRING'
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
                }
                STRING,
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
            ],
            'indent'     => [
                <<<'STRING'
                type Query {
                        test: [Test]!
                    }

                    type Test {
                        c: C!
                        b: B
                    }

                    extend type Test
                    implements
                        & B
                        & A
                    {
                        a: String
                        b: [B!]!
                    }

                    enum C {
                        B
                        C
                        A
                    }
                STRING,
                $settings,
                1,
                0,
                $document,
            ],
            'filter'     => [
                <<<'STRING'
                type Query {
                    test: [Test]!
                }

                type Test {
                    c: C!
                    @c
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
                STRING,
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
            ],
        ];
    }
    // </editor-fold>
}
