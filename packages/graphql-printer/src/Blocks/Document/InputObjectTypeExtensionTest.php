<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeExtensionNode;
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
#[CoversClass(InputObjectTypeExtension::class)]
#[CoversClass(InputFieldsDefinition::class)]
class InputObjectTypeExtensionTest extends TestCase {
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
        $context = new Context($settings, null, $schema);
        $actual  = (new InputObjectTypeExtension($context, $level, $used, $definition))->serialize($level, $used);

        if ($expected) {
            Parser::inputObjectTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = Parser::inputObjectTypeExtension('extend input A @a { a: A @b }');
        $block      = new InputObjectTypeExtension($context, 0, 0, $definition);
        $content    = $block->serialize(0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $block->getUsedDirectives());

        $ast = new InputObjectTypeExtension($context, 0, 0, Parser::inputObjectTypeExtension($content));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
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
                <<<'STRING'
                extend input Test
                @b
                @a
                STRING,
                $settings,
                0,
                0,
                Parser::inputObjectTypeExtension(
                    'extend input Test @b @a',
                ),
                null,
            ],
            'fields'              => [
                <<<'STRING'
                extend input Test {
                    a: String
                }
                STRING,
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
                <<<'STRING'
                extend input Test
                @a
                {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                Parser::inputObjectTypeExtension(
                    'extend input Test @a { a: String }',
                ),
                null,
            ],
            'indent'              => [
                <<<'STRING'
                extend input Test
                    @a
                    {
                        """
                        Description
                        """
                        a: String
                    }
                STRING,
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
                <<<'STRING'
                extend input Test
                @a
                {
                    a: String
                    b: B
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
                Parser::inputObjectTypeExtension(
                    'extend input Test @a @b { a: String, b: B }',
                ),
                null,
            ],
            'filter'              => [
                <<<'STRING'
                extend input Test
                @a
                {
                    a: String
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
                Parser::inputObjectTypeExtension(
                    'extend input Test @a @b { a: String, b: B }',
                ),
                BuildSchema::build(
                    <<<'STRING'
                    scalar B
                    STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
