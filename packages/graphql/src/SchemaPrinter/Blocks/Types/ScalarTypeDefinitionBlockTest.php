<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\ScalarType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\ScalarTypeDefinitionBlock
 */
class ScalarTypeDefinitionBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        ScalarType $type,
    ): void {
        $actual = (string) (new ScalarTypeDefinitionBlock(new Dispatcher(), $settings, $level, $used, $type));
        $parsed = Parser::scalarTypeDefinition($actual);

        self::assertEquals($expected, $actual);
        self::assertInstanceOf(ScalarTypeDefinitionNode::class, $parsed);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, DirectiveNode}>
     */
    public function dataProviderToString(): array {
        return [
            'scalar'                          => [
                <<<'STRING'
                scalar Test
                STRING,
                new DefaultSettings(),
                0,
                0,
                new CustomScalarType([
                    'name' => 'Test',
                ]),
            ],
            'with description and directives' => [
                <<<'STRING'
                """
                Description
                """
                scalar Test
                @a
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }

                    public function isIncludeDirectivesInDescription(): bool {
                        return false;
                    }
                },
                0,
                0,
                new CustomScalarType([
                    'name'        => 'Test',
                    'description' => 'Description',
                    'astNode'     => Parser::scalarTypeDefinition(
                        <<<'STRING'
                        scalar Test @a
                        STRING,
                    ),
                ]),
            ],
            'with directives in description'  => [
                <<<'STRING'
                """
                Description

                @a
                """
                scalar Test
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return false;
                    }

                    public function isIncludeDirectivesInDescription(): bool {
                        return true;
                    }
                },
                0,
                0,
                new CustomScalarType([
                    'name'        => 'Test',
                    'description' => 'Description',
                    'astNode'     => Parser::scalarTypeDefinition(
                        <<<'STRING'
                        scalar Test @a
                        STRING,
                    ),
                ]),
            ],
            'indent'                          => [
                <<<'STRING'
                """
                    Description
                    """
                    scalar Test
                    @a(
                        value: "very very long value"
                    )
                    @b(value: "b")
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }

                    public function isIncludeDirectivesInDescription(): bool {
                        return false;
                    }
                },
                1,
                60,
                new CustomScalarType([
                    'name'        => 'Test',
                    'description' => 'Description',
                    'astNode'     => Parser::scalarTypeDefinition(
                        <<<'STRING'
                        scalar Test @a(value: "very very long value") @b(value: "b")
                        STRING,
                    ),
                ]),
            ],
            'indent + no description'         => [
                <<<'STRING'
                scalar Test
                    @a(
                        value: "very very long value"
                    )
                    @b(value: "b")
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }

                    public function isIncludeDirectivesInDescription(): bool {
                        return false;
                    }
                },
                1,
                60,
                new CustomScalarType([
                    'name'        => 'Test',
                    'astNode'     => Parser::scalarTypeDefinition(
                        <<<'STRING'
                        scalar Test @a(value: "very very long value") @b(value: "b")
                        STRING,
                    ),
                ]),
            ],
        ];
    }
    // </editor-fold>
}
