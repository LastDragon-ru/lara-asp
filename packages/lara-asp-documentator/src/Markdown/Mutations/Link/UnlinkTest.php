<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Unlink::class)]
#[CoversClass(Base::class)]
final class UnlinkTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text [self][self] text text [self](#fragment) text text text
            text text ![image][image] text text ![image](#fragment).

            [self]: #fragment
            [link]: ./path/to/file.md
            [image]: ./#fragment

            # Special

            ## Autolink

            Autolink <https://example.com/> link link.

            ## Inside Quote

            > Text text [self][self] text text [self](#fragment) text text text

            ## Inside Table

            | Header                  |  [Header][link]               |
            |-------------------------|-------------------------------|
            | Cell [link][self] cell. | Cell `\|` \\| ![table][image] |
            | Cell                    | Cell `\|` \\| [table][self].  |
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $actual   = (string) $document->mutate(new Unlink());

        self::assertSame(
            <<<'MARKDOWN'
            # Header

            Text text link text text `link` text
            text text self text text self text text text
            text text ![image][image] text text ![image](#fragment).

            [self]: #fragment
            [link]: ./path/to/file.md
            [image]: ./#fragment

            # Special

            ## Autolink

            Autolink https://example.com/ link link.

            ## Inside Quote

            > Text text self text text self text text text

            ## Inside Table

            | Header                  |  Header               |
            |-------------------------|-------------------------------|
            | Cell link cell. | Cell `\|` \\| ![table][image] |
            | Cell                    | Cell `\|` \\| table.  |

            MARKDOWN,
            $actual,
        );
    }
}
