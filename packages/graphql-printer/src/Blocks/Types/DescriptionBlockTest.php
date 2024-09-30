<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function implode;

/**
 * @internal
 */
#[CoversClass(DescriptionBlock::class)]
final class DescriptionBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        ?string $description,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new DescriptionBlock($context, $description))->serialize($collector, $level, $used);

        self::assertEquals($expected, $actual);

        if ($expected !== '') {
            Parser::valueLiteral($actual);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ?string}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setAlwaysMultilineArguments(false)
            ->setNormalizeDescription(false);

        return [
            'null'                                                     => [
                '',
                $settings,
                0,
                0,
                null,
            ],
            'Prints an empty string'                                   => [
                '',
                $settings,
                0,
                0,
                '',
            ],
            'Prints an empty string (normalized)'                      => [
                '',
                $settings->setNormalizeDescription(true),
                0,
                0,
                '',
            ],
            'Prints an empty string with only whitespace'              => [
                '" "',
                $settings,
                0,
                0,
                ' ',
            ],
            'Prints an empty string with only whitespace (normalized)' => [
                '',
                $settings->setNormalizeDescription(true),
                0,
                0,
                ' ',
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
            ],
            'Leading space'                                            => [
                <<<'STRING'
                """  Leading space"""
                STRING,
                $settings,
                0,
                0,
                '  Leading space',
            ],
            'Leading tab'                                              => [
                "\"\"\"\tLeading tab\"\"\"",
                $settings,
                0,
                0,
                "\tLeading tab",
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
            ],
        ];
    }
    //</editor-fold>
}
