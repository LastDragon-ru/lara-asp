<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(EnumTypeExtension::class)]
#[CoversClass(EnumValuesDefinition::class)]
class EnumTypeExtensionTest extends TestCase {
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
        EnumTypeExtensionNode $type,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new EnumTypeExtension($context, $type))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::enumTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
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
                <<<'STRING'
                extend enum Test {
                    C
                    B
                    A
                }
                STRING,
                $settings,
                0,
                0,
                Parser::enumTypeExtension(
                    'extend enum Test { C B A }',
                ),
            ],
            'indent'     => [
                <<<'STRING'
                extend enum Test {
                        A
                        B
                        C
                    }
                STRING,
                $settings
                    ->setNormalizeEnums(true),
                1,
                0,
                Parser::enumTypeExtension(
                    'extend enum Test { C B A }',
                ),
            ],
            'directives' => [
                <<<'STRING'
                extend enum Test
                @a
                {
                    A
                }
                STRING,
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
