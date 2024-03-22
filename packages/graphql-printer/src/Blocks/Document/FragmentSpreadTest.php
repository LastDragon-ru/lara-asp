<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(FragmentSpread::class)]
final class FragmentSpreadTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        FragmentSpreadNode $definition,
        TypeNode|Type|null $type,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new FragmentSpread($context, $definition, $type))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::fragment($actual);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @param array{types: array<string, string>, directives: array<string, string>} $expected
     * @param (TypeNode&Node)|Type|null                                              $type
     */
    #[DataProvider('dataProviderStatistics')]
    public function testStatistics(
        array $expected,
        FragmentSpreadNode $definition,
        TypeNode|Type|null $type,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context(new TestSettings(), null, $schema);
        $block     = new FragmentSpread($context, $definition, $type);
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals($expected['types'], $collector->getUsedTypes());
        self::assertEquals($expected['directives'], $collector->getUsedDirectives());

        $astFragmentNode = Parser::fragment($content);

        self::assertInstanceOf(FragmentSpreadNode::class, $astFragmentNode);

        $astCollector = new Collector();
        $astBlock     = new FragmentSpread($context, $astFragmentNode, $type);

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      array{types: array<string, string>, directives: array<string, string>},
     *      InlineFragmentNode|FragmentSpreadNode,
     *      (TypeNode&Node)|Type|null,
     *      ?Schema,
     *      }>
     */
    public static function dataProviderStatistics(): array {
        return [
            'schema'    => [
                [
                    'types'      => ['A' => 'A'],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragment('... Test @a'),
                Parser::typeReference('A'),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type A {
                        a: Int
                    }

                    type B {
                        b: String
                    }
                    GRAPHQL,
                ),
            ],
            'no schema' => [
                [
                    'types'      => ['A' => 'A'],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragment('... Test @a'),
                Parser::typeReference('A'),
                null,
            ],
            'no type'   => [
                [
                    'types'      => [],
                    'directives' => ['@a' => '@a'],
                ],
                Parser::fragment('... Test @a'),
                null,
                null,
            ],
        ];
    }

    /**
     * @return array<string,array{
     *      string,
     *      Settings,
     *      int,
     *      int,
     *      InlineFragmentNode|FragmentSpreadNode,
     *      (TypeNode&Node)|Type|null,
     *      ?Schema,
     *      }>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false)
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);

        return [
            'spread'                      => [
                <<<'STRING'
                ... A
                @b
                @a
                STRING,
                $settings,
                0,
                0,
                Parser::fragment(
                    '... A @b @a',
                ),
                null,
                null,
            ],
            'indent'                      => [
                <<<'STRING'
                ... A
                    @b
                    @a
                STRING,
                $settings,
                1,
                120,
                Parser::fragment(
                    '... A @b @a',
                ),
                null,
                null,
            ],
            'filter (directive)'          => [
                <<<'STRING'
                ... A
                @a
                STRING,
                $settings
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::fragment(
                    '... A @b @a',
                ),
                null,
                null,
            ],
            'filter (no schema, no type)' => [
                <<<'STRING'
                ... A
                @a
                @b
                STRING,
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragment(
                    '... A @a @b',
                ),
                null,
                null,
            ],
            'filter (schema, no type)'    => [
                <<<'STRING'
                ... A
                @a
                @b
                STRING,
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragment(
                    '... A @a @b',
                ),
                null,
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type B {
                        b: String
                    }
                    GRAPHQL,
                ),
            ],
            'filter (no schema, type)'    => [
                <<<'STRING'
                ... A
                @a
                @b
                STRING,
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragment(
                    '... A @a @b',
                ),
                Parser::typeReference('B'),
                null,
            ],
            'filter'                      => [
                '',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::fragment(
                    '... A @a @b',
                ),
                Parser::typeReference('A'),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    type A {
                        a: Int
                    }
                    GRAPHQL,
                ),
            ],
        ];
    }
    // </editor-fold>
}
