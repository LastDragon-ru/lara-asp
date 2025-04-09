<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Comment;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Remove::class)]
final class RemoveTest extends TestCase {
    public function testInvoke(): void {
        $content = <<<'MARKDOWN'
            # Header

            <!-- comment -->

            Text text text text text text text text text text text
            text text <!-- comment --> <!-- comment --> text text
            text text text text.

            <!-- comment -->
            [//]: # (comment)

            Text text text text text <b>text</b> text text text.

            [//]: # "also comment"

            <!--
            comment
            -->
            MARKDOWN;

        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new Remove());

        self::assertSame(
            <<<'MARKDOWN'
            # Header

            Text text text text text text text text text text text
            text text   text text
            text text text text.

            Text text text text text <b>text</b> text text text.

            MARKDOWN,
            $actual,
        );
    }
}
