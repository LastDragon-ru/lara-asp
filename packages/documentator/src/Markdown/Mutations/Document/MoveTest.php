<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Move::class)]
final class MoveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, ?string $path, string $content, string $target): void {
        $path     = $path !== null ? new FilePath($path) : null;
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, $path);
        $actual   = (string) $document->mutate(new Move(new FilePath($target)));

        self::assertSame($expected, $actual);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, ?string, string, string}>
     */
    public static function dataProviderInvoke(): array {
        return [
            // General
            'From `null`'                  => [
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                null,
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                'relative/path/to/file.md',
            ],
            'Same'                         => [
                <<<'MARKDOWN'
                [foo]: /path "title"
                MARKDOWN,
                '/path/file.md',
                <<<'MARKDOWN'
                [foo]: /path "title"
                MARKDOWN,
                '/path/file.md',
            ],
            'Empty'                        => [
                <<<'MARKDOWN'
                [foo]: # "title"

                MARKDOWN,
                '/path/from/file.md',
                <<<'MARKDOWN'
                [foo]: . "title"
                MARKDOWN,
                '/path/to/file.md',
            ],
            'Query & Fragment'             => [
                <<<'MARKDOWN'
                [foo]: ../from/path?a=123#fragment
                [bar]: ?a=123#fragment

                MARKDOWN,
                '/path/from/file.md',
                <<<'MARKDOWN'
                [foo]: path?a=123#fragment
                [bar]: file.md?a=123#fragment
                MARKDOWN,
                '/path/to/file.md',
            ],
            'References'                   => [
                <<<'MARKDOWN'
                # General

                [tel]: tel:+70000000000 "title"
                [self-fragment]: #fragment
                [self-file]: #fragment
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

                [c]:
                    ../from/file/c
                    (
                        title
                    )

                # Special

                ## Target escaping

                [title]: <../from/\<file\>/ /a>
                [title]: <../from/file/ /a>

                ## Title escaping

                [title]: ../file/a "title with ( ) and with ' '"
                [title]: ../file/a (title with \( \) and with ' ')
                [title]: ../file/a "title with ( ) and with ' ' and with \" \""

                ## Inside Quote

                > [quote]: ../file/a
                >
                > [quote]:
                > ../from/file/b
                > (title)

                MARKDOWN,
                '/path/from/file.md',
                <<<'MARKDOWN'
                # General

                [tel]: tel:+70000000000 "title"
                [self-fragment]: #fragment
                [self-file]: ./file.md#fragment
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
                '/path/to/file.md',
            ],
            'Links'                        => [
                <<<'MARKDOWN'
                # General

                Text text [tel](tel:+70000000000 "title") text [link](../from/file/a)
                text [_`link`_](../from/file/b ' <title> ') text [title](<../from/file/a> (title))
                [mailto](mailto:mail@example.com) text [absolute](/path/to/file 'title')
                text [external](https://example.com/) text [self](#fragment)
                text [self](#fragment).

                # Special

                ## Target escaping

                Text [title](<../from/\<file\>/ /a>) text [title](<../from/file/ /a>).

                ## Title escaping

                Text [title](../file/a "title with ( ) and with ' '" ) text
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
                '/path/from/file.md',
                <<<'MARKDOWN'
                # General

                Text text [tel](tel:+70000000000 "title") text [link](./file/a)
                text [_`link`_](file/b ' <title> ') text [title](<./file/a> (title))
                [mailto](mailto:mail@example.com) text [absolute](/path/to/file 'title')
                text [external](https://example.com/) text [self](#fragment)
                text [self](file.md#fragment).

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
                '/path/to/file.md',
            ],
            'Images'                       => [
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

                ![image](<../from/\<file\>/ /a>)

                ## Title escaping

                Text ![title](../file/a "title with ( ) and with ' '" ) text
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
                '/path/from/file.md',
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
                '/path/to/file.md',
            ],
            'Footnotes'                    => [
                <<<'MARKDOWN'
                # General

                Text text[^1] text text[^note] text[^quote].

                # Special

                ## Inside Quote

                > Text text[^1] text text[^note] text[^quote].
                >
                > [^quote]: Text text [tel](tel:+70000000000 "title") text [link](../from/file/a)
                >     text [absolute](/path/to/file 'title') text [link](../from/file/b)

                [^1]: Text text text [link](../from/file/a)

                [^note]: Text text [tel](tel:+70000000000 "title") text [link](../from/file/a)
                    text [absolute](/path/to/file 'title') text [link](../from/file/b)

                MARKDOWN,
                '/path/from/file.md',
                <<<'MARKDOWN'
                # General

                Text text[^1] text text[^note] text[^quote].

                # Special

                ## Inside Quote

                > Text text[^1] text text[^note] text[^quote].
                >
                > [^quote]: Text text [tel](tel:+70000000000 "title") text [link](./file/a)
                >     text [absolute](/path/to/file 'title') text [link](file/b)

                [^1]: Text text text [link](./file/a)

                [^note]: Text text [tel](tel:+70000000000 "title") text [link](./file/a)
                    text [absolute](/path/to/file 'title') text [link](file/b)
                MARKDOWN,
                '/path/to/file.md',
            ],
            'Move to url with white space' => [
                <<<'MARKDOWN'
                # Links

                Text text [tel](tel:+70000000000 "title") text [link](../path/from/file/a)
                text [_`link`_](../path/from/file/b ' <title> ') text [title](<../path/from/file/a> (title))
                [mailto](mailto:mail@example.com) text [absolute](/path/to/file 'title')
                text [external](https://example.com/) text [self](#fragment)
                text [self](#fragment).

                Text [title](<../path/from/\<file\>/ /a>) text [title](<../path/from/file/ /a>).

                # Images

                Text ![external](https://example.com/) text ![image](<../path/from/file/a> (title))
                text ![image](../path/from/file/b ' <title> ').

                ![image](<../path/from/\<file\>/ /a>)

                # References

                [tel]: tel:+70000000000 "title"
                [self-fragment]: #fragment
                [self-file]: #fragment
                [link]: ../path/from/file/a
                [link]: ../path/from/file/b ' <title> '
                [title]: <../path/from/file/a> (title)

                MARKDOWN,
                '/path/from/file.md',
                <<<'MARKDOWN'
                # Links

                Text text [tel](tel:+70000000000 "title") text [link](./file/a)
                text [_`link`_](file/b ' <title> ') text [title](<./file/a> (title))
                [mailto](mailto:mail@example.com) text [absolute](/path/to/file 'title')
                text [external](https://example.com/) text [self](#fragment)
                text [self](file.md#fragment).

                Text [title](./%3Cfile%3E/%20/a) text [title](<./file/ /a>).

                # Images

                Text ![external](https://example.com/) text ![image](<../from/file/a> (title))
                text ![image](../from/file/b ' <title> ').

                ![image](../from/%3Cfile%3E/%20/a)

                # References

                [tel]: tel:+70000000000 "title"
                [self-fragment]: #fragment
                [self-file]: ./file.md#fragment
                [link]: ./file/a
                [link]: file/b ' <title> '
                [title]: <./file/a> (title)
                MARKDOWN,
                '/path with whitespaces/file.md',
            ],
        ];
    }
    // </editor-fold>
}
