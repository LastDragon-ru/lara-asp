<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Prefix::class)]
#[CoversClass(Base::class)]
final class PrefixTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            ![image][image]

            [link]: https://example.com
            [image]: https://example.com

            # Special

            ## Inside Quote

            > ![image][link]

            ## Inside Table

            | Header                  |  [Header][link]               |
            |-------------------------|-------------------------------|
            | Cell [link][link] cell. | Cell `\|` \\| ![table][image] |
            | Cell                    | Cell cell [table][link].      |
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $actual   = (string) $document->mutate(new Prefix('prefix'));

        self::assertSame(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][prefix-link] text
            text text ![image][prefix-image] text text.

            ![image][prefix-image]

            [prefix-link]: https://example.com
            [prefix-image]: https://example.com

            # Special

            ## Inside Quote

            > ![image][prefix-link]

            ## Inside Table

            | Header                  |  [Header][prefix-link]               |
            |-------------------------|-------------------------------|
            | Cell [link][prefix-link] cell. | Cell `\|` \\| ![table][prefix-image] |
            | Cell                    | Cell cell [table][prefix-link].      |

            MARKDOWN,
            $actual,
        );
    }
}
