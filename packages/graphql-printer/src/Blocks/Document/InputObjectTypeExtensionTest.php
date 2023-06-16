<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\Parser;
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
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        InputObjectTypeExtensionNode $definition,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new InputObjectTypeExtension(
            $context,
            $level,
            $used,
            $definition,
        ));

        if ($expected) {
            Parser::inputObjectTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = Parser::inputObjectTypeExtension('extend input A @a { a: A @b }');
        $block      = new InputObjectTypeExtension($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['A' => 'A'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $block->getUsedDirectives());

        $ast = new InputObjectTypeExtension($context, 0, 0, Parser::inputObjectTypeExtension((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, InputObjectTypeExtensionNode}>
     */
    public static function dataProviderToString(): array {
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
            ],
        ];
    }
    // </editor-fold>
}
