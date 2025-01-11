<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(MakeSplittable::class)]
final class MakeSplittableTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Footnotes must be removed[^1]

            Text text text[^2] text text [^1] text text text [^2] text text text
            text text[^1] text text text [^2].

            [^1]: footnote 1
            [^2]: footnote 2

            # References must be inlined

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            [link]: https://example.com
            [image]: https://example.com

            # Links to the self must be removed

            Text text [link](https://example.com) text text [`link`][link] text
            text text [self][self] text text [self](#fragment).

            [self]: #fragment
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath(__FILE__));
        $actual   = (string) $document->mutate(new MakeSplittable());

        self::assertSame(
            <<<'MARKDOWN'
            # Footnotes must be removed

            Text text text text text  text text text  text text text
            text text text text text .

            # References must be inlined

            Text text [link](https://example.com) text text [`link`](https://example.com) text
            text text ![image](https://example.com) text text.

            # Links to the self must be removed

            Text text [link](https://example.com) text text [`link`](https://example.com) text
            text text self text text self.

            MARKDOWN,
            $actual,
        );
    }
}
