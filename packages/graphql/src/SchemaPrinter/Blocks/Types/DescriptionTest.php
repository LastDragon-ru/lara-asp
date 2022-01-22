<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast\DirectiveNodeList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

use function implode;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\Description
 */
class DescriptionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
     * @dataProvider dataProviderToString
     *
     * @param array<DirectiveNode>|null $directives
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        ?string $description,
        ?array $directives,
    ): void {
        $dispatcher = new Dispatcher();
        $directives = new DirectiveNodeList($dispatcher, $settings, $level, $used, $directives);
        $actual     = (string) (new Description($dispatcher, $settings, $level, $used, $description, $directives));

        self::assertEquals($expected, $actual);

        if ($expected) {
            Parser::valueLiteral($actual);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ?string, array<DirectiveNode>|null}>
     */
    public function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeDescription(false);

        return [
            'null'                                                     => [
                '',
                $settings,
                0,
                0,
                null,
                null,
            ],
            'Prints an empty string'                                   => [
                '',
                $settings,
                0,
                0,
                '',
                [],
            ],
            'Prints an empty string (normalized)'                      => [
                '',
                $settings->setNormalizeDescription(true),
                0,
                0,
                '',
                [],
            ],
            'Prints an empty string with only whitespace'              => [
                '" "',
                $settings,
                0,
                0,
                ' ',
                [],
            ],
            'Prints an empty string with only whitespace (normalized)' => [
                '',
                $settings->setNormalizeDescription(true),
                0,
                0,
                ' ',
                [],
            ],
            'One-line prints a short string'                           => [
                <<<'STRING'
                """
                Short string
                """
                STRING,
                $settings,
                0,
                0,
                'Short string',
                [],
            ],
            'One-line prints a long string'                            => [
                <<<'STRING'
                """
                Long string
                """
                STRING,
                $settings->setLineLength(4),
                0,
                0,
                'Long string',
                [],
            ],
            'String is short (indent)'                                 => [
                <<<'STRING'
                """
                    string
                    """
                STRING,
                $settings
                    ->setIndent('  ')
                    ->setLineLength(2),
                2,
                0,
                'string',
                [],
            ],
            'String is long (indent)'                                  => [
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
                [],
            ],
            'Multi-line string'                                        => [
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
                [],
            ],
            'Multi-line string (normalized)'                           => [
                <<<'STRING'
                """
                aaa
                  bbb

                ccc
                """
                STRING,
                $settings->setNormalizeDescription(true),
                0,
                0,
                <<<'STRING'
                aaa
                  bbb


                ccc
                STRING,
                [],
            ],
            'Leading space'                                            => [
                <<<'STRING'
                """  Leading space"""
                STRING,
                $settings,
                0,
                0,
                '  Leading space',
                [],
            ],
            'Leading tab'                                              => [
                "\"\"\"\tLeading tab\"\"\"",
                $settings,
                0,
                0,
                "\tLeading tab",
                [],
            ],
            'Trailing "'                                               => [
                <<<'STRING'
                """
                Trailing "
                """
                STRING,
                $settings,
                0,
                0,
                'Trailing "',
                [],
            ],
            'Leading whitespace and trailing "'                        => [
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
                [],
            ],
            'Trailing backslash'                                       => [
                <<<'STRING'
                """
                Trailing \\
                """
                STRING,
                $settings,
                0,
                0,
                'Trailing \\\\',
                [],
            ],
            'Escape wrapper'                                           => [
                <<<'STRING'
                """
                String with \""" wrapper
                """
                STRING,
                $settings,
                0,
                0,
                'String with """ wrapper',
                [],
            ],
            'Indent'                                                   => [
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
                [],
            ],
            'Indent (normalized)'                                      => [
                implode(
                    "\n",
                    [
                        '"""',
                        '        aaa',
                        '',
                        '      bbb',
                        '    ccc',
                        '',
                        '      ddd',
                        '    """',
                    ],
                ),
                $settings
                    ->setIndent('  ')
                    ->setNormalizeDescription(true),
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
                [],
            ],
            'directives (disabled)'                                    => [
                <<<'STRING'
                """
                Description
                """
                STRING,
                $settings,
                0,
                0,
                <<<'STRING'
                Description
                STRING,
                [
                    Parser::directive('@a'),
                ],
            ],
            'directives (enabled)'                                     => [
                <<<'STRING'
                """
                Description



                @a
                @b(test: "abc")
                """
                STRING,
                $settings->setPrintDirectivesInDescription(true),
                0,
                0,
                <<<'STRING'
                Description


                STRING,
                [
                    Parser::directive('@a'),
                    Parser::directive('@b(test: "abc")'),
                ],
            ],
            'directives (enabled) + normalized'                        => [
                <<<'STRING'
                """
                Description

                @a
                @b(test: "abc")
                """
                STRING,
                $settings
                    ->setNormalizeDescription(true)
                    ->setPrintDirectivesInDescription(true),
                0,
                0,
                <<<'STRING'
                Description


                STRING,
                [
                    Parser::directive('@a'),
                    Parser::directive('@b(test: "abc")'),
                ],
            ],
            'empty description + directives (enabled) + normalized'    => [
                <<<'STRING'
                """
                @a
                @b(test: "abc")
                """
                STRING,
                $settings
                    ->setNormalizeDescription(true)
                    ->setPrintDirectivesInDescription(true),
                0,
                0,
                '',
                [
                    Parser::directive('@a'),
                    Parser::directive('@b(test: "abc")'),
                ],
            ],
        ];
    }
    //</editor-fold>
}
