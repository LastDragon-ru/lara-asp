<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RemoveUnused::class)]
final class RemoveUnusedTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            Text text text text [`link`][link] text text text[^note].

            [link]: https://example.com
            [image]: https://example.com
            [image]: https://example.com "2"
            [unused]: https://example.com "title"

            [^note]: Text text text
                text text text

            > [unused-quote]: https://example.com "title"
            >
            > > [unused-2]: https://example.com
            > >

            !(image)[image]

            [^1]: Unused but not reference
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath('path/to/file.md'));
        $actual   = (string) $document->mutate(new RemoveUnused());

        self::assertSame(
            <<<'MARKDOWN'
            Text text text text [`link`][link] text text text[^note].

            [link]: https://example.com
            [image]: https://example.com
            [^note]: Text text text
                text text text


            !(image)[image]

            [^1]: Unused but not reference

            MARKDOWN,
            $actual,
        );
    }
}
