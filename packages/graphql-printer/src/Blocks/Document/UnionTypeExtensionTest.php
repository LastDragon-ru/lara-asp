<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(UnionTypeExtension::class)]
#[CoversClass(UnionMemberTypes::class)]
class UnionTypeExtensionTest extends TestCase {
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
        UnionTypeExtensionNode $type,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new UnionTypeExtension($context, $type))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::unionTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $union     = Parser::unionTypeExtension('extend union Test @a = A | B');
        $context   = new Context(new TestSettings(), null, null);
        $collector = new Collector();
        $block     = new UnionTypeExtension($context, $union);
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A', 'B' => 'B'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new UnionTypeExtension($context, Parser::unionTypeExtension($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, UnionTypeExtensionNode, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeUnions(false)
            ->setAlwaysMultilineUnions(false);

        return [
            'single-line'            => [
                <<<'STRING'
                extend union Test = C | B | A
                STRING,
                $settings,
                0,
                0,
                Parser::unionTypeExtension(
                    'extend union Test = C | B | A',
                ),
                null,
            ],
            'multiline'              => [
                <<<'STRING'
                extend union Test =
                    | A
                    | B
                    | C
                STRING,
                $settings
                    ->setNormalizeUnions(true),
                0,
                120,
                Parser::unionTypeExtension(
                    'extend union Test = C | B | A',
                ),
                null,
            ],
            'indent'                 => [
                <<<'STRING'
                extend union Test =
                        | C
                        | B
                        | A
                STRING,
                $settings,
                1,
                120,
                Parser::unionTypeExtension(
                    'extend union Test = C | B | A',
                ),
                null,
            ],
            'multiline always'       => [
                <<<'STRING'
                extend union Test =
                    | C
                    | B
                    | A
                STRING,
                $settings
                    ->setAlwaysMultilineUnions(true),
                0,
                0,
                Parser::unionTypeExtension(
                    'extend union Test = C | B | A',
                ),
                null,
            ],
            'directives'             => [
                <<<'STRING'
                extend union Test
                @a
                = C | B | A
                STRING,
                $settings,
                0,
                0,
                Parser::unionTypeExtension(
                    'extend union Test @a = C | B | A',
                ),
                null,
            ],
            'directives + multiline' => [
                <<<'STRING'
                extend union Test
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
                Parser::unionTypeExtension(
                    'extend union Test @a = C | B | A',
                ),
                null,
            ],
            'filter: definition'     => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::unionTypeExtension(
                    'extend union Test @a @b = B | A',
                ),
                null,
            ],
            'filter: no schema'      => [
                <<<'STRING'
                extend union Test
                @a
                = B | A
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
                Parser::unionTypeExtension(
                    'extend union Test @a @b = B | A',
                ),
                null,
            ],
            'filter'                 => [
                <<<'STRING'
                extend union Test
                @a
                = A
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
                Parser::unionTypeExtension(
                    'extend union Test @a @b = B | A',
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
