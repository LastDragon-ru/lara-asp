<?php

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\Description
 */
class DescriptionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
     * @dataProvider dataProviderToString
     */
    public function testToString(string $expected, Settings $settings, int $level, string $description): void {
        self::assertEquals($expected, (string)(new Description($settings, $level, $description)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, string}>
     */
    public function dataProviderToString(): array {
        return [
            'Prints an empty description'                             => [
                '""""""',
                new class() extends DefaultSettings {
                    public function isNormalizeDescription(): bool {
                        return false;
                    }
                },
                0,
                '',
            ],
            'Prints an empty description (normalized)'                => [
                '',
                new class() extends DefaultSettings {
                    public function isNormalizeDescription(): bool {
                        return true;
                    }
                },
                0,
                '',
            ],
            'Prints an description with only whitespace'              => [
                '" "',
                new class() extends DefaultSettings {
                    public function isNormalizeDescription(): bool {
                        return false;
                    }
                },
                0,
                ' ',
            ],
            'Prints an description with only whitespace (normalized)' => [
                '',
                new class() extends DefaultSettings {
                    public function isNormalizeDescription(): bool {
                        return true;
                    }
                },
                0,
                ' ',
            ],
            'One-line prints a short description'                     => [
                '"""Short description"""',
                new DefaultSettings(),
                0,
                'Short description',
            ],
            'One-line prints a long description'                      => [
                <<<'STRING'
                """
                Long description
                """
                STRING,
                new class() extends DefaultSettings {
                    public function getLineLength(): int {
                        return 4;
                    }
                },
                0,
                'Long description',
            ],
            'Description is short'                                    => [
                <<<'STRING'
                """description"""
                STRING,
                new class() extends DefaultSettings {
                    public function getLineLength(): int {
                        return 17;
                    }
                },
                0,
                'description',
            ],
            'Description is short (indent)'                           => [
                <<<'STRING'
                    """description"""
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
                'description',
            ],
            'Description is long (indent)'                            => [
                <<<'STRING'
                    """
                    description
                    """
                STRING,
                new class() extends DefaultSettings {
                    public function getLineLength(): int {
                        return 21 - 1;
                    }

                    public function getIndent(): string {
                        return '  ';
                    }
                },
                2,
                'description',
            ],
            'Multi-line description'                                  => [
                "\"\"\"\nMulti-line\n   description  \n\n\n\"\"\"",
                new class() extends DefaultSettings {
                    public function isNormalizeDescription(): bool {
                        return false;
                    }
                },
                0,
                "Multi-line\n   description  \n\n",
            ],
            'Multi-line description (normalized)'                     => [
                <<<'STRING'
                """
                Multi-line
                   description
                """
                STRING,
                new class() extends DefaultSettings {
                    public function isNormalizeDescription(): bool {
                        return true;
                    }
                },
                0,
                "Multi-line\n   description  \n\n",
            ],
            'Leading space'                                           => [
                <<<'STRING'
                """
                  Leading space
                """
                STRING,
                new DefaultSettings(),
                0,
                "  Leading space",
            ],
            'Leading tab'                                             => [
                "\"\"\"\n\tLeading tab\n\"\"\"",
                new DefaultSettings(),
                0,
                "\tLeading tab",
            ],
            'Trailing "'                                              => [
                <<<'STRING'
                """
                Trailing "
                """
                STRING,
                new DefaultSettings(),
                0,
                'Trailing "',
            ],
            'Trailing backslashes'                                    => [
                <<<'STRING'
                """
                Trailing \\
                """
                STRING,
                new DefaultSettings(),
                0,
                'Trailing \\\\',
            ],
            'Escape wrapper'                                          => [
                <<<'STRING'
                """String with \""" wrapper"""
                STRING,
                new DefaultSettings(),
                0,
                'String with """ wrapper',
            ],
            'Indent'                                                  => [
                <<<'STRING'
                    """
                      Multi-line
                    description
                    """
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '  ';
                    }
                },
                2,
                <<<'STRING'
                  Multi-line
                description
                STRING,
            ],
        ];
    }
    //</editor-fold>
}
