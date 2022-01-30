<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

use function implode;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\StringBlock
 */
class StringBlockTest extends TestCase {
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
        string $string,
    ): void {
        $settings = new BlockSettings($this->app->make(DirectiveResolver::class), $settings);
        $actual   = (string) new StringBlock($settings, $level, $used, $string);
        $parsed   = Parser::valueLiteral($actual);

        self::assertInstanceOf(StringValueNode::class, $parsed);
        self::assertEquals($expected, $actual);
        self::assertEquals($string, $parsed->value);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, string}>
     */
    public function dataProviderToString(): array {
        $settings = new TestSettings();

        return [
            'Prints an empty string'                => [
                '""""""',
                $settings,
                0,
                0,
                '',
            ],
            'Prints an string with only whitespace' => [
                '" "',
                $settings,
                0,
                0,
                ' ',
            ],
            'One-line prints a short string'        => [
                '"""Short string"""',
                $settings,
                0,
                0,
                'Short string',
            ],
            'One-line prints a long string'         => [
                <<<'STRING'
                """
                Long string
                """
                STRING,
                $settings->setLineLength(4),
                0,
                0,
                'Long string',
            ],
            'String is short (indent)'              => [
                <<<'STRING'
                """string"""
                STRING,
                $settings->setLineLength(21),
                2,
                0,
                'string',
            ],
            'String is long (indent)'               => [
                <<<'STRING'
                """
                    string
                    """
                STRING,
                $settings
                    ->setIndent('  ')
                    ->setLineLength(22),
                2,
                20,
                'string',
            ],
            'Multi-line string'                     => [
                <<<'STRING'
                """
                aaa
                  bbb

                ccc
                """
                STRING,
                $settings,
                0,
                0,
                <<<'STRING'
                aaa
                  bbb

                ccc
                STRING,
            ],
            'Leading space'                         => [
                <<<'STRING'
                """  Leading space"""
                STRING,
                $settings,
                0,
                0,
                '  Leading space',
            ],
            'Leading tab'                           => [
                "\"\"\"\tLeading tab\"\"\"",
                $settings,
                0,
                0,
                "\tLeading tab",
            ],
            'Leading whitespace (single line)'      => [
                "\"\"\"\tLeading tab\"\"\"",
                $settings->setLineLength(1),
                0,
                0,
                "\tLeading tab",
            ],
            'Trailing "'                            => [
                <<<'STRING'
                """
                Trailing "
                """
                STRING,
                $settings,
                0,
                0,
                'Trailing "',
            ],
            'Leading whitespace and trailing "'     => [
                <<<'STRING'
                """
                  Leading whitespace and trailing "
                abc
                """
                STRING,
                $settings,
                0,
                0,
                <<<'STRING'
                  Leading whitespace and trailing "
                abc
                STRING,
            ],
            'Trailing backslash'                    => [
                <<<'STRING'
                """
                Trailing \\
                """
                STRING,
                $settings,
                0,
                0,
                'Trailing \\\\',
            ],
            'Escape wrapper'                        => [
                <<<'STRING'
                """String with \""" wrapper"""
                STRING,
                $settings,
                0,
                0,
                'String with """ wrapper',
            ],
            'Indent'                                => [
                implode(
                    "\n",
                    [
                        '"""',
                        '        aaa',
                        '',
                        '      bbb  ',
                        '    ccc    ',
                        '      ',
                        '      ddd  ',
                        '    """',
                    ],
                ),
                $settings->setIndent('  '),
                2,
                0,
                implode(
                    "\n",
                    [
                        '    aaa',
                        '',
                        '  bbb  ',
                        'ccc    ',
                        '  ',
                        '  ddd  ',
                    ],
                ),
            ],
        ];
    }
    //</editor-fold>
}
