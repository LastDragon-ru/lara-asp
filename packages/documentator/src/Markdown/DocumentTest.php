<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Document::class)]
final class DocumentTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testGetTitle(): void {
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                ## Header A
                # Header B
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertEquals(
            'Header',
            (new Document(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertEquals(
            'Header',
            (new Document(
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
    }

    public function testGetSummary(): void {
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertNull(
            (new Document(
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            'fsdfsdfsdf',
            (new Document(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            <<<'TEXT'
            fsdfsdfsdf
            fsdfsdfsdf
            TEXT,
            (new Document(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            <<<'TEXT'
            fsdfsdfsdf
            fsdfsdfsdf
            TEXT,
            (new Document(
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
    }

    public function testIsEmpty(): void {
        self::assertFalse(
            (new Document(
                <<<'MARKDOWN'
                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->isEmpty(),
        );
        self::assertFalse(
            (new Document(
                <<<'MARKDOWN'
                [unused]: ../path/to/file
                MARKDOWN,
            ))
                ->isEmpty(),
        );
        self::assertFalse(
            (new Document(
                <<<'MARKDOWN'
                <!-- comment -->
                MARKDOWN,
            ))
                ->isEmpty(),
        );
        self::assertTrue(
            (new Document(
                <<<'MARKDOWN'



                MARKDOWN,
            ))
                ->isEmpty(),
        );
    }

    #[DataProvider('dataProviderSetPath')]
    public function testSetPath(string $expected, ?string $path, string $content, ?string $target): void {
        self::assertEquals($expected, (string) (new Document($content, $path))->setPath($target));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, ?string, string, ?string}>
     */
    public static function dataProviderSetPath(): array {
        return [
            // General
            'from `null`' => [
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                null,
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                'relative/path/to',
            ],
            'to `null`'   => [
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                'relative/path/from',
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                null,
            ],
            'same'        => [
                <<<'MARKDOWN'
                [foo]: /path "title"
                MARKDOWN,
                '/path',
                <<<'MARKDOWN'
                [foo]: /path "title"
                MARKDOWN,
                '/path',
            ],
            // References
            'references'  => [
                <<<'MARKDOWN'
                # General

                [tel]: tel:+70000000000 "title"
                [link]: ../from/file/a
                [link]: ../from/file/b (title)
                [title]: ../from/file/a (title)
                [unused]: ../path/to/file
                [mailto]: mailto:mail@example.com
                [absolute]: /path/to/file 'title'
                [external]: https://example.com/

                [a]: ../from/file/a
                [a]: ../from/file/b

                [b]: ../from/file/b (
                abc
                123
                )

                [c]: ../from/file/c (
                        title
                    )

                # Special

                ## Title escaping

                ### can be avoided

                [title]: ../file/a "title with ( ) and with ' '"
                [title]: ../file/a "title with ( ) and with ' '"

                ### cannot

                [title]: ../file/a (title with \\( \\) and with ' ' and with " ")

                ## Inside Quote

                > [quote]: ../file/a
                >
                > [quote]: ../from/file/b (title)
                MARKDOWN,
                '/path/from',
                <<<'MARKDOWN'
                # General

                [tel]: tel:+70000000000 "title"
                [link]: ./file/a
                [link]: file/b 'title'
                [title]: <./file/a> (title)
                [unused]: ../path/to/file
                [mailto]: mailto:mail@example.com
                [absolute]: /path/to/file 'title'
                [external]: https://example.com/

                [a]: file/a
                [a]: file/b

                [b]: file/b "
                abc
                123
                "

                [c]:
                    file/c
                    (
                        title
                    )

                # Special

                ## Title escaping

                ### can be avoided

                [title]: ../file/a "title with ( ) and with ' '"
                [title]: ../file/a (title with \( \) and with ' ')

                ### cannot

                [title]: ../file/a "title with ( ) and with ' ' and with \" \""

                ## Inside Quote

                > [quote]: ../file/a
                >
                > [quote]:
                > ./file/b
                > (title)
                MARKDOWN,
                '/path/to',
            ],
        ];
    }
    // </editor-fold>
}
