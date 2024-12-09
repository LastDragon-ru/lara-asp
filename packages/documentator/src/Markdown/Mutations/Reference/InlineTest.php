<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Inline::class)]
final class InlineTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            ![image][image]

            [link]: https://example.com
            [image]: https://example.com (image)
            [table]: https://example.com (table | cell)

            # Special

            ## Inside Quote

            > ![image][link]

            ## Inside Table

            | Header                  |  [Header][link]               |
            |-------------------------|-------------------------------|
            | Cell [link][link] cell. | Cell `\|` \\| ![table][table] |
            | Cell                    | Cell cell ![table][link].     |
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new Inline());

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text [link](https://example.com) text text [`link`](https://example.com) text
            text text ![image](https://example.com "image") text text.

            ![image](https://example.com "image")

            # Special

            ## Inside Quote

            > ![image](https://example.com)

            ## Inside Table

            | Header                  |  [Header](https://example.com)               |
            |-------------------------|-------------------------------|
            | Cell [link](https://example.com) cell. | Cell `\|` \\| ![table](https://example.com "table \| cell") |
            | Cell                    | Cell cell ![table](https://example.com).     |

            MARKDOWN,
            $actual,
        );
    }
}
