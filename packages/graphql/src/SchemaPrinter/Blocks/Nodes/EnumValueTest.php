<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumValueDefinition;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes\EnumValue
 */
class EnumValueTest extends TestCase {
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
        EnumValueDefinition $type,
    ): void {
        $actual = (string) (new EnumValue(new Dispatcher(), $settings, $level, $used, $type));
        $parsed = Parser::enumValueDefinition($actual);

        self::assertEquals($expected, $actual);
        self::assertInstanceOf(EnumValueDefinitionNode::class, $parsed);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, DirectiveNode}>
     */
    public function dataProviderToString(): array {
        return [
            'value'                      => [
                <<<'STRING'
                A
                STRING,
                new DefaultSettings(),
                0,
                0,
                new EnumValueDefinition([
                    'name'  => 'A',
                    'value' => 'A',
                ]),
            ],
            'indent'                     => [
                <<<'STRING'
                A
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
                1,
                0,
                new EnumValueDefinition([
                    'name'  => 'A',
                    'value' => 'A',
                ]),
            ],
            'description and directives' => [
                <<<'STRING'
                """
                Description
                """
                A
                @a
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }

                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
                0,
                0,
                new EnumValueDefinition([
                    'name'        => 'A',
                    'value'       => 'A',
                    'astNode'     => Parser::enumValueDefinition('A @a'),
                    'description' => 'Description',
                ]),
            ],
        ];
    }
    // </editor-fold>
}
