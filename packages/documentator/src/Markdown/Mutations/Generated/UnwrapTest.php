<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Unwrap::class)]
final class UnwrapTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            [//]: # (start: block)

            Text text text text text text text text text text text text text
            text text text text text text text text text text text text text
            text text text text text text text text text text text text.

            [//]: # (start: nested)

            Nested should be ignored.

            [//]: # (end: nested)

            [//]: # (end: block)

            > [//]: # (start: quote)
            > should work
            > [//]: # (end: quote)

            [//]: # (start: block)

            > Text text text.
            >
            > [//]: # (start: nested)
            > Nested should be ignored.
            > [//]: # (end: nested)

            [//]: # (end: block)

            [//]: # (start: without end)
            [//]: # (warning: Generated automatically. Do not edit.)

            Up to the end.
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new Unwrap());

        self::assertEquals(
            <<<'MARKDOWN'
            # Header

            Text text text text text text text text text text text text text
            text text text text text text text text text text text text text
            text text text text text text text text text text text text.

            [//]: # (start: nested)

            Nested should be ignored.

            [//]: # (end: nested)

            > should work

            > Text text text.
            >
            > [//]: # (start: nested)
            > Nested should be ignored.
            > [//]: # (end: nested)

            Up to the end.

            MARKDOWN,
            $actual,
        );
    }
}
