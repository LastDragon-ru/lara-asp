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
            'from `null`'    => [
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                null,
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                'relative/path/to',
            ],
            'to `null`'      => [
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                'relative/path/from',
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                null,
            ],
            'same'           => [
                <<<'MARKDOWN'
                [foo]: /path "title"
                MARKDOWN,
                '/path',
                <<<'MARKDOWN'
                [foo]: /path "title"
                MARKDOWN,
                '/path',
            ],
            'query&fragment' => [
                <<<'MARKDOWN'
                [foo]: ../from/path?a=123#fragment
                MARKDOWN,
                '/path/from',
                <<<'MARKDOWN'
                [foo]: path?a=123#fragment
                MARKDOWN,
                '/path/to',
            ],
            'references'     => [
                <<<'MARKDOWN'
                # General

                [tel]: tel:+70000000000 "title"
                [link]: ../from/file/a
                [link]: ../from/file/b ' <title> '
                [title]: <../from/file/a> (title)
                [unused]: ../path/to/file
                [mailto]: mailto:mail@example.com
                [absolute]: /path/to/file 'title'
                [external]: https://example.com/

                [a]: ../from/file/a
                [a]: ../from/file/b

                [b]: ../from/file/b "
                abc
                123
                "

                [c]: ../from/file/c (
                        title
                    )

                # Special

                ## Target escaping

                [title]: ../from/%3Cfile%3E/%20/a
                [title]: <../from/file/ /a>

                ## Title escaping

                [title]: ../file/a "title with ( ) and with ' '"
                [title]: ../file/a (title with \( \) and with ' ')
                [title]: ../file/a "title with ( ) and with ' ' and with \" \""

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
                [link]: file/b ' <title> '
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

                ## Target escaping

                [title]: ./%3Cfile%3E/%20/a
                [title]: <./file/ /a>

                ## Title escaping

                [title]: ../file/a "title with ( ) and with ' '"
                [title]: ../file/a (title with \( \) and with ' ')
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
            'links'          => [
                <<<'MARKDOWN'
                # General

                Text text [tel](tel:+70000000000 "title") text [link](../from/file/a)
                text [link](../from/file/b ' <title> ') text [title](<../from/file/a> (title))
                [mailto](mailto:mail@example.com) text [absolute](/path/to/file 'title')
                text [external](https://example.com/).

                # Special

                ## Target escaping

                Text [title](../from/%3Cfile%3E/%20/a) text [title](<../from/file/ /a>).

                ## Title escaping

                Text [title](../file/a "title with ( ) and with ' '") text
                text [title](../file/a (title with \( \) and with ' ')) text
                text [title](../file/a "title with ( ) and with ' ' and with \" \"").

                ## Inside Quote

                > Text [quote](../file/a) text [quote](https://example.com/)
                > text text [quote](../from/file/b (title)).

                ## Inside Table

                | Header                  |  Header ([table](../from/file/b))                                     |
                |-------------------------|-----------------------------------------------------------------|
                | Cell [link][link] cell. | Cell `\|` \\| [table](<../from/file\|a> "\|")                           |
                | Cell                    | Cell cell [table](https://example.com/) cell [table](../from/file/a). |
                MARKDOWN,
                '/path/from',
                <<<'MARKDOWN'
                # General

                Text text [tel](tel:+70000000000 "title") text [link](./file/a)
                text [link](file/b ' <title> ') text [title](<./file/a> (title))
                [mailto](mailto:mail@example.com) text [absolute](/path/to/file 'title')
                text [external](https://example.com/).

                # Special

                ## Target escaping

                Text [title](./%3Cfile%3E/%20/a) text [title](<./file/ /a>).

                ## Title escaping

                Text [title]( ../file/a "title with ( ) and with ' '" ) text
                text [title]( ../file/a (title with \( \) and with ' ')) text
                text [title](../file/a "title with ( ) and with ' ' and with \" \"").

                ## Inside Quote

                > Text [quote](../file/a) text [quote](https://example.com/)
                > text text [quote](file/b (title)).

                ## Inside Table

                | Header                  |  Header ([table](./file/b))                                     |
                |-------------------------|-----------------------------------------------------------------|
                | Cell [link][link] cell. | Cell `\|` \\| [table](<file\|a> "\|")                           |
                | Cell                    | Cell cell [table](https://example.com/) cell [table](./file/a). |
                MARKDOWN,
                '/path/to',
            ],
            'images'         => [
                <<<'MARKDOWN'
                # General

                ![image](<../from/file/a> (title))
                ![image](../from/file/b ' <title> ')

                ![external](https://example.com/)
                ![absolute](/path/to/file 'title')

                Text ![external](https://example.com/) text ![image](<../from/file/a> (title))
                text ![image](../from/file/b ' <title> ').

                # Special

                ## Target escaping

                ![image](../from/%3Cfile%3E/%20/a)

                ## Title escaping

                Text ![title](../file/a "title with ( ) and with ' '") text
                text ![title](../file/a (title with \( \) and with ' ')) text
                text ![title](../file/a "title with ( ) and with ' ' and with \" \"").

                ## Inside Quote

                > ![quote](../from/file/a)

                ## Inside Table

                | Header                  |  Header (![table](../from/file/b))                                      |
                |-------------------------|-------------------------------------------------------------------|
                | Cell [link][link] cell. | Cell `\|` \\| ![table](<../from/file\|a> "\|")                            |
                | Cell                    | Cell cell ![table](https://example.com/) cell ![table](../from/file/a). |
                MARKDOWN,
                '/path/from',
                <<<'MARKDOWN'
                # General

                ![image](<./file/a> (title))
                ![image](file/b ' <title> ')

                ![external](https://example.com/)
                ![absolute](/path/to/file 'title')

                Text ![external](https://example.com/) text ![image](<./file/a> (title))
                text ![image](file/b ' <title> ').

                # Special

                ## Target escaping

                ![image](./%3Cfile%3E/%20/a)

                ## Title escaping

                Text ![title]( ../file/a "title with ( ) and with ' '" ) text
                text ![title]( ../file/a (title with \( \) and with ' ')) text
                text ![title](../file/a "title with ( ) and with ' ' and with \" \"").

                ## Inside Quote

                > ![quote](file/a)

                ## Inside Table

                | Header                  |  Header (![table](./file/b))                                      |
                |-------------------------|-------------------------------------------------------------------|
                | Cell [link][link] cell. | Cell `\|` \\| ![table](<file\|a> "\|")                            |
                | Cell                    | Cell cell ![table](https://example.com/) cell ![table](./file/a). |
                MARKDOWN,
                '/path/to',
            ],
        ];
    }
    // </editor-fold>
}
