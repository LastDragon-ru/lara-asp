<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\ScalarType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ScalarTypeDefinition::class)]
final class ScalarTypeDefinitionTest extends TestCase {
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
        ScalarTypeDefinitionNode|ScalarType $type,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new ScalarTypeDefinition($context, $type))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::scalarTypeDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ScalarTypeDefinitionNode|ScalarType}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setAlwaysMultilineArguments(false)
            ->setPrintDirectives(false);

        return [
            'scalar'                  => [
                <<<'GRAPHQL'
                scalar Test
                GRAPHQL,
                $settings,
                0,
                0,
                new CustomScalarType([
                    'name' => 'Test',
                ]),
            ],
            'scalar + directives'     => [
                <<<'GRAPHQL'
                scalar Test
                @a
                @b
                @c
                GRAPHQL,
                $settings->setPrintDirectives(true),
                0,
                0,
                new CustomScalarType([
                    'name'              => 'Test',
                    'astNode'           => Parser::scalarTypeDefinition('scalar Test @a'),
                    'extensionASTNodes' => [
                        Parser::scalarTypeExtension('extend scalar Test @b'),
                        Parser::scalarTypeExtension('extend scalar Test @c'),
                    ],
                ]),
            ],
            'indent'                  => [
                <<<'GRAPHQL'
                """
                    Description
                    """
                    scalar Test
                    @a(
                        value: "very very long value"
                    )
                    @b(value: "b")
                GRAPHQL,
                $settings
                    ->setLineLength(20)
                    ->setPrintDirectives(true),
                1,
                60,
                new CustomScalarType([
                    'name'        => 'Test',
                    'description' => 'Description',
                    'astNode'     => Parser::scalarTypeDefinition(
                        <<<'GRAPHQL'
                        scalar Test @a(value: "very very long value") @b(value: "b")
                        GRAPHQL,
                    ),
                ]),
            ],
            'indent + no description' => [
                <<<'GRAPHQL'
                scalar Test
                    @a(value: "very very long value")
                    @b(value: "b")
                GRAPHQL,
                $settings->setPrintDirectives(true),
                1,
                60,
                new CustomScalarType([
                    'name'    => 'Test',
                    'astNode' => Parser::scalarTypeDefinition(
                        <<<'GRAPHQL'
                        scalar Test @a(value: "very very long value") @b(value: "b")
                        GRAPHQL,
                    ),
                ]),
            ],
            'filter'                  => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                new CustomScalarType([
                    'name' => 'Test',
                ]),
            ],
            'ast'                     => [
                <<<'GRAPHQL'
                """
                Description
                """
                scalar Test
                @a
                GRAPHQL,
                $settings
                    ->setPrintDirectives(true)
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::scalarTypeDefinition(
                    '"Description" scalar Test @a @b',
                ),
            ],
            'ast + filter'            => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::scalarTypeDefinition(
                    'scalar Test',
                ),
            ],
        ];
    }
    // </editor-fold>
}
