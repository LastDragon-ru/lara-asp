<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Editor;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_key_first;
use function array_values;

/**
 * @internal
 */
#[CoversClass(Move::class)]
final class MoveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, ?string $path, string $content, string $target): void {
        $mutation = new Move($target);
        $document = new class($content, $path) extends Document {
            #[Override]
            public function getNode(): DocumentNode {
                return parent::getNode();
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getLines(): array {
                return parent::getLines();
            }
        };
        $node     = $document->getNode();
        $lines    = $document->getLines();
        $offset   = (int) array_key_first($lines);
        $changes  = $mutation($document, $node);
        $actual   = (string) (new Editor(array_values($lines), $offset))->mutate($changes);

        self::assertEquals($expected, $actual);
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
            'from `null`'    => [
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                null,
                <<<'MARKDOWN'
                [foo]: relative/path/from "title"
                MARKDOWN,
                'relative/path/to/file.md',
            ],
            'same'           => [
                <<<'MARKDOWN'
                [foo]: /path "title"
                MARKDOWN,
                '/path/file.md',
                <<<'MARKDOWN'
                [foo]: /path "title"
                MARKDOWN,
                '/path/file.md',
            ],
            'query&fragment' => [
                <<<'MARKDOWN'
                [foo]: ../from/path?a=123#fragment
                MARKDOWN,
                '/path/from/file.md',
                <<<'MARKDOWN'
                [foo]: path?a=123#fragment
                MARKDOWN,
                '/path/to/file.md',
            ],
            'references'     => [
                <<<'MARKDOWN'
                # General

                [tel]: tel:+70000000000 "title"
                [self]: #fragment
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
                '/path/from/file.md',
                <<<'MARKDOWN'
                # General

                [tel]: tel:+70000000000 "title"
                [self]: #fragment
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
            'links'          => [
                <<<'MARKDOWN'
                # General

                Text text [tel](tel:+70000000000 "title") text [link](../from/file/a)
                text [_`link`_](../from/file/b ' <title> ') text [title](<../from/file/a> (title))
                [mailto](mailto:mail@example.com) text [absolute](/path/to/file 'title')
                text [external](https://example.com/) text [self](#fragment).

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
                '/path/from/file.md',
                <<<'MARKDOWN'
                # General

                Text text [tel](tel:+70000000000 "title") text [link](./file/a)
                text [_`link`_](file/b ' <title> ') text [title](<./file/a> (title))
                [mailto](mailto:mail@example.com) text [absolute](/path/to/file 'title')
                text [external](https://example.com/) text [self](#fragment).

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
            'footnotes'      => [
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
        ];
    }
    // </editor-fold>
}
