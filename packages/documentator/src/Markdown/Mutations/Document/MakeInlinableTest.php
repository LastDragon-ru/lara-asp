<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(MakeInlinable::class)]
final class MakeInlinableTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Footnotes must be prefixed[^1]

            Text text text[^2] text text [^1] text text text [^2] text text text
            text text[^1] text text text [^2].

            [^1]: footnote 1
            [^2]: footnote 2

            [^3]: Footnote unused

            # References must be prefixed

            Text text [link](https://example.com) text text [`link`][link] text
            text text ![image][image] text text.

            [link]: https://example.com
            [image]: https://example.com

            [unused]: https://example.com (Reference unused)
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath(__FILE__));
        $actual   = (string) $document->mutate(new MakeInlinable('prefix'));

        self::assertSame(
            <<<'MARKDOWN'
            # Footnotes must be prefixed[^prefix-1]

            Text text text[^prefix-2] text text [^prefix-1] text text text [^prefix-2] text text text
            text text[^prefix-1] text text text [^prefix-2].

            [^prefix-1]: footnote 1
            [^prefix-2]: footnote 2

            # References must be prefixed

            Text text [link](https://example.com) text text [`link`][prefix-link] text
            text text ![image][prefix-image] text text.

            [prefix-link]: https://example.com
            [prefix-image]: https://example.com

            MARKDOWN,
            $actual,
        );
    }
}
