<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Prefix::class)]
final class PrefixTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header[^1]

            Text text text[^2] text text [^1] text text text [^2] text text text
            text text[^1] text text text [^2] text text text [^3] text[^bignote].

            [^1]: footnote 1

            Text text text[^2].

            [^2]: footnote 2

            [^4]: footnote 4

            [^bignote]: Text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.

                Text text text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content, new FilePath(__FILE__));
        $actual   = (string) $document->mutate(new Prefix('prefix'));

        self::assertSame(
            <<<'MARKDOWN'
            # Header[^prefix-1]

            Text text text[^prefix-2] text text [^prefix-1] text text text [^prefix-2] text text text
            text text[^prefix-1] text text text [^prefix-2] text text text [^3] text[^prefix-bignote].

            [^prefix-1]: footnote 1

            Text text text[^prefix-2].

            [^prefix-2]: footnote 2

            [^prefix-4]: footnote 4

            [^prefix-bignote]: Text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.

                Text text text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.

            MARKDOWN,
            $actual,
        );
    }
}
