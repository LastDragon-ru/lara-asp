<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(InputObjectTypeExtension::class)]
#[CoversClass(InputFieldsDefinition::class)]
final class InputObjectTypeExtensionTest extends TestCase {
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
        InputObjectTypeExtensionNode $definition,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new InputObjectTypeExtension($context, $definition))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::inputObjectTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::inputObjectTypeExtension('extend input A @a { a: A @b }');
        $block      = new InputObjectTypeExtension($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new InputObjectTypeExtension($context, Parser::inputObjectTypeExtension($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InputObjectTypeExtensionNode, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(true)
            ->setNormalizeFields(false);

        return [
            'directives'          => [
                <<<'GRAPHQL'
                extend input Test
                @b
                @a
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::inputObjectTypeExtension(
                    'extend input Test @b @a',
                ),
                null,
            ],
            'fields'              => [
                <<<'GRAPHQL'
                extend input Test {
                    a: String
                }
                GRAPHQL,
                $settings
                    ->setPrintDirectives(false),
                0,
                0,
                Parser::inputObjectTypeExtension(
                    'extend input Test @a { a: String }',
                ),
                null,
            ],
            'fields + directives' => [
                <<<'GRAPHQL'
                extend input Test
                @a
                {
                    a: String
                }
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::inputObjectTypeExtension(
                    'extend input Test @a { a: String }',
                ),
                null,
            ],
            'indent'              => [
                <<<'GRAPHQL'
                extend input Test
                    @a
                    {
                        """
                        Description
                        """
                        a: String
                    }
                GRAPHQL,
                $settings,
                1,
                120,
                Parser::inputObjectTypeExtension(
                    'extend input Test @a { "Description" a: String }',
                ),
                null,
            ],
            'filter: definition'  => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::inputObjectTypeExtension(
                    'extend input Test @a { a: String }',
                ),
                null,
            ],
            'filter (no schema)'  => [
                <<<'GRAPHQL'
                extend input Test
                @a
                {
                    a: String
                    b: B
                }
                GRAPHQL,
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
                Parser::inputObjectTypeExtension(
                    'extend input Test @a @b { a: String, b: B }',
                ),
                null,
            ],
            'filter'              => [
                <<<'GRAPHQL'
                extend input Test
                @a
                {
                    a: String
                }
                GRAPHQL,
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
                Parser::inputObjectTypeExtension(
                    'extend input Test @a @b { a: String, b: B }',
                ),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    scalar B
                    GRAPHQL,
                ),
            ],
        ];
    }
    // </editor-fold>
}
