<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Cleanup::class)]
final class CleanupTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            Text text text[^2] text text text text [`link`][link] text
            text text ![image][image] text text.

            [^1]: footnote 1
            [^2]: Unused footnote
            [link]: https://example.com
            [image]: https://example.com
            [unused]: https://example.com (Unused reference)

            [reference]: https://example.com (Reference is unused, because `^footnote` is not used)
            [^footnote]: [footnote][reference]

            <!-- comment -->
            [//]: # (comment)
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath(__FILE__));
        $actual   = (string) $document->mutate(new Cleanup());

        self::assertSame(
            <<<'MARKDOWN'
            Text text text[^2] text text text text [`link`][link] text
            text text ![image][image] text text.

            [^2]: Unused footnote
            [link]: https://example.com
            [image]: https://example.com
            [reference]: https://example.com (Reference is unused, because `^footnote` is not used)

            MARKDOWN,
            $actual,
        );
    }
}
