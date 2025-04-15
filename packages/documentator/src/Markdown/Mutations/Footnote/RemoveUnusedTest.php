<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RemoveUnused::class)]
final class RemoveUnusedTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header[^1]

            Text text text[^2] text text.

            [^1]: footnote 1

            Text text text[^2].

            [^2]: footnote 2
            [^3]: footnote 4
            [^4]: footnote 4

            [^bignote]: Text text text text text text text text text text text
                text text text text text text text text text text text text text
                text.

            > [^unused]: Inside quote.
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new RemoveUnused());

        self::assertSame(
            <<<'MARKDOWN'
            # Header[^1]

            Text text text[^2] text text.

            [^1]: footnote 1

            Text text text[^2].

            [^2]: footnote 2

            MARKDOWN,
            $actual,
        );
    }
}
