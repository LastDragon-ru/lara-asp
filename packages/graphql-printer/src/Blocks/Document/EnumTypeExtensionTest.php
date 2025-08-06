<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\TestCase;
use LastDragon_ru\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(EnumTypeExtension::class)]
#[CoversClass(EnumValuesDefinition::class)]
final class EnumTypeExtensionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        EnumTypeExtensionNode $type,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new EnumTypeExtension($context, $type))->serialize($collector, $level, $used);

        if ($expected !== '') {
            Parser::enumTypeExtension($actual);
        }

        self::assertSame($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::enumTypeExtension(
            'extend enum Test @a { A }',
        );
        $block      = new EnumTypeExtension($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals([], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new EnumTypeExtension($context, Parser::enumTypeExtension($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, EnumTypeExtensionNode}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeEnums(false);

        return [
            'enum'       => [
                <<<'GRAPHQL'
                extend enum Test {
                    C
                    B
                    A
                }
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::enumTypeExtension(
                    'extend enum Test { C B A }',
                ),
            ],
            'indent'     => [
                <<<'GRAPHQL'
                extend enum Test {
                        A
                        B
                        C
                    }
                GRAPHQL,
                $settings
                    ->setNormalizeEnums(true),
                1,
                0,
                Parser::enumTypeExtension(
                    'extend enum Test { C B A }',
                ),
            ],
            'directives' => [
                <<<'GRAPHQL'
                extend enum Test
                @a
                {
                    A
                }
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::enumTypeExtension(
                    'extend enum Test @a { A }',
                ),
            ],
            'filter'     => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::enumTypeExtension(
                    'extend enum Test { A }',
                ),
            ],
        ];
    }
    // </editor-fold>
}
