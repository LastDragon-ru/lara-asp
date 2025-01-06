<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Text::class)]
final class TextTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array{string, int} $expected
     * @param int<0, max>        $level
     */
    #[DataProvider('dataProviderSetPadding')]
    public function testSetPadding(array $expected, string $text, int $level, string $padding): void {
        $actualCuts = 0;
        $actualText = Text::setPadding($text, $level, $padding, $actualCuts);

        self::assertEquals($expected[0], $actualText);
        self::assertEquals($expected[1], $actualCuts);
    }

    public function testGetLines(): void {
        self::assertEquals(
            [
                '',
            ],
            Text::getLines(''),
        );
        self::assertEquals(
            [
                'line',
            ],
            Text::getLines('line'),
        );
        self::assertEquals(
            [
                'a',
                '',
                'b',
                'c',
                'd',
                '',
                'e',
            ],
            Text::getLines("a\n\nb\r\nc\nd\r\re"),
        );
    }

    public function testGetPathTitle(): void {
        self::assertEquals('file', Text::getPathTitle('path/to/file.txt'));
        self::assertEquals('file Name', Text::getPathTitle('path/to/fileName.txt'));
        self::assertEquals('File name second', Text::getPathTitle('path/to/File name.second.txt'));
        self::assertEquals('File name', Text::getPathTitle('path/to/File     name'));
    }

    public function testSetEol(): void {
        self::assertEquals("a\nb\nc\n", Text::setEol("a\r\nb\r\nc\r\n"));
        self::assertEquals("a\rb\rc\r", Text::setEol("a\r\nb\r\nc\r\n", "\r"));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{array{string, int}, string, int<0, max>, string}>
     */
    public static function dataProviderSetPadding(): array {
        return [
            'space: padding should be added'    => [
                [
                    <<<'TEXT'
                        # Header

                        fsdfsdfsdf
                        fsdfsdfsdf

                            fsdfsdfsdf

                        * a
                          * a.a
                          * a.b
                        * b
                    TEXT,
                    0,
                ],
                <<<'TEXT'
                # Header

                fsdfsdfsdf
                fsdfsdfsdf

                    fsdfsdfsdf

                * a
                  * a.a
                  * a.b
                * b
                TEXT,
                4,
                ' ',
            ],
            'space: padding should be removed'  => [
                [
                    <<<'TEXT'
                    # Header

                    fsdfsdfsdf
                    fsdfsdfsdf

                        fsdfsdfsdf

                    * a
                      * a.a
                      * a.b
                    * b
                    TEXT,
                    4,
                ],
                <<<'TEXT'
                    # Header

                    fsdfsdfsdf
                    fsdfsdfsdf

                        fsdfsdfsdf

                    * a
                      * a.a
                      * a.b
                    * b
                TEXT,
                0,
                ' ',
            ],
            'space: no changes'                 => [
                [
                    <<<'TEXT'
                    fsdfsdfsdf
                        fsdfsdfsdf
                    TEXT,
                    0,
                ],
                <<<'TEXT'
                fsdfsdfsdf
                    fsdfsdfsdf
                TEXT,
                0,
                ' ',
            ],
            'string: padding should be added'   => [
                [
                    <<<'TEXT'
                    > > # Header
                    > >
                    > > fsdfsdfsdf
                    > > fsdfsdfsdf
                    >
                    > text
                    TEXT,
                    0,
                ],
                <<<'TEXT'
                > # Header
                >
                > fsdfsdfsdf
                > fsdfsdfsdf

                text
                TEXT,
                1,
                '> ',
            ],
            'string: padding should be removed' => [
                [
                    <<<'TEXT'
                    > # Header
                    >
                    > fsdfsdfsdf
                    > fsdfsdfsdf

                    text
                    TEXT,
                    1,
                ],
                <<<'TEXT'
                > > # Header
                > >
                > > fsdfsdfsdf
                > > fsdfsdfsdf
                >
                > text
                TEXT,
                0,
                '> ',
            ],
            'string: no changes'                => [
                [
                    <<<'TEXT'
                    fsdfsdfsdf
                    > fsdfsdfsdf
                    TEXT,
                    0,
                ],
                <<<'TEXT'
                fsdfsdfsdf
                > fsdfsdfsdf
                TEXT,
                0,
                '> ',
            ],
        ];
    }
    // </editor-fold>
}
