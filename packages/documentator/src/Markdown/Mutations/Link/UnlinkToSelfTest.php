<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(UnlinkToSelf::class)]
#[CoversClass(Mutation::class)]
final class UnlinkToSelfTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text [self][self-a] text text [self](./file.md#fragment) text text text
            text text [self][self-b] text text [self](./#fragment) text text text
            text text [self][self-c] text text [self](#fragment) text text text
            text text [self][self-d] text text [self](./file.md) text text text
            text text ![image][image] text text ![image](#fragment).

            [self-a]: ./file.md#fragment
            [self-b]: ./#fragment
            [self-c]: #fragment
            [self-d]: ./file.md
            [link]: ./path/to/file.md
            [image]: ./#fragment

            # Special

            ## Inside Quote

            > Text text [link](https://example.com) text text [`link`][link] text
            > text text [self][self-a] text text [self](./file.md#fragment) text text text
            > text text [self][self-b] text text [self](./#fragment) text text text
            > text text [self][self-c] text text [self](#fragment) text text text
            > text text [self][self-d] text text [self](./file.md) text text text

            ## Inside Table

            | Header                    |  [Header][link]               |
            |---------------------------|-------------------------------|
            | Cell [link][self-a] cell. | Cell `\|` \\| ![table][image] |
            | Cell                      | Cell cell [table][self-a].    |
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $actual   = (string) $document->mutate(new UnlinkToSelf());

        self::assertSame(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text self text text self text text text
            text text [self][self-b] text text [self](./#fragment) text text text
            text text self text text self text text text
            text text self text text self text text text
            text text ![image][image] text text ![image](#fragment).

            [self-a]: ./file.md#fragment
            [self-b]: ./#fragment
            [self-c]: #fragment
            [self-d]: ./file.md
            [link]: ./path/to/file.md
            [image]: ./#fragment

            # Special

            ## Inside Quote

            > Text text [link](https://example.com) text text [`link`][link] text
            > text text self text text self text text text
            > text text [self][self-b] text text [self](./#fragment) text text text
            > text text self text text self text text text
            > text text self text text self text text text

            ## Inside Table

            | Header                    |  [Header][link]               |
            |---------------------------|-------------------------------|
            | Cell link cell. | Cell `\|` \\| ![table][image] |
            | Cell                      | Cell cell table.    |

            MARKDOWN,
            $actual,
        );
    }
}
