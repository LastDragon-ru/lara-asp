<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use function implode;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\StringBlock
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
        int $reserved,
        string $string,
    ): void {
        $actual = (string) (new class($settings, $level, $reserved, $string) extends StringBlock {
            public function __construct(
                Settings $settings,
                int $level,
                int $reserved,
                protected string $string,
            ) {
                parent::__construct($settings, $level, $reserved);
            }

            protected function getString(): string {
                return $this->string;
            }
        });
        $parsed = Parser::valueLiteral($actual)->value;

        self::assertEquals($expected, $actual);
        self::assertEquals($string, $parsed);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, string}>
     */
    public function dataProviderToString(): array {
        return [
            'Prints an empty string'                => [
                '""""""',
                new DefaultSettings(),
                0,
                0,
                '',
            ],
            'Prints an string with only whitespace' => [
                '" "',
                new DefaultSettings(),
                0,
                0,
                ' ',
            ],
            'One-line prints a short string'        => [
                '"""Short string"""',
                new DefaultSettings(),
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
                new class() extends DefaultSettings {
                    public function getLineLength(): int {
                        return 4;
                    }
                },
                0,
                0,
                'Long string',
            ],
            'String is short (indent)'              => [
                <<<'STRING'
                    """string"""
                STRING,
                new class() extends DefaultSettings {
                    public function getLineLength(): int {
                        return 21;
                    }

                    public function getIndent(): string {
                        return '  ';
                    }
                },
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
                new class() extends DefaultSettings {
                    public function getLineLength(): int {
                        return 22;
                    }

                    public function getIndent(): string {
                        return '  ';
                    }
                },
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
                new DefaultSettings(),
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
                new DefaultSettings(),
                0,
                0,
                '  Leading space',
            ],
            'Leading tab'                           => [
                "\"\"\"\tLeading tab\"\"\"",
                new DefaultSettings(),
                0,
                0,
                "\tLeading tab",
            ],
            'Leading whitespace (single line)'      => [
                "\"\"\"\tLeading tab\"\"\"",
                new class() extends DefaultSettings {
                    public function getLineLength(): int {
                        return 1;
                    }
                },
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
                new DefaultSettings(),
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
                new DefaultSettings(),
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
                new DefaultSettings(),
                0,
                0,
                'Trailing \\\\',
            ],
            'Escape wrapper'                        => [
                <<<'STRING'
                """String with \""" wrapper"""
                STRING,
                new DefaultSettings(),
                0,
                0,
                'String with """ wrapper',
            ],
            'Indent'                                => [
                implode(
                    "\n",
                    [
                        '    """',
                        '        aaa',
                        '',
                        '      bbb  ',
                        '    ccc    ',
                        '      ',
                        '      ddd  ',
                        '    """',
                    ],
                ),
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '  ';
                    }
                },
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
