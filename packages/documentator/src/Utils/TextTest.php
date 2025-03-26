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

        self::assertSame($expected[0], $actualText);
        self::assertSame($expected[1], $actualCuts);
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
        self::assertSame('File', Text::getPathTitle('path/to/file.txt'));
        self::assertSame('File name', Text::getPathTitle('path/to/file-name.txt'));
        self::assertSame('File Name', Text::getPathTitle('path/to/fileName.txt'));
        self::assertSame('File name second', Text::getPathTitle('path/to/File name.second.txt'));
        self::assertSame('File name', Text::getPathTitle('path/to/File     name'));
    }

    public function testSetEol(): void {
        self::assertSame("a\nb\nc\n", Text::setEol("a\r\nb\r\nc\r\n"));
        self::assertSame("a\rb\rc\r", Text::setEol("a\r\nb\r\nc\r\n", "\r"));
    }

    public function testIsMultiline(): void {
        self::assertFalse(Text::isMultiline(''));
        self::assertFalse(Text::isMultiline('a'));
        self::assertTrue(Text::isMultiline("a\nb\nc\n"));
        self::assertTrue(Text::isMultiline("a\r\nb\r\nc\r\n"));
    }

    public function testToSingleLine(): void {
        self::assertSame('', Text::toSingleLine(''));
        self::assertSame('abc', Text::toSingleLine('abc'));
        self::assertSame('abc', Text::toSingleLine("\nabc"));
        self::assertSame('a b c', Text::toSingleLine("\na\n\nb\n\nc\n"));
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
