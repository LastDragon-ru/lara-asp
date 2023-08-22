<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Markdown::class)]
class MarkdownTest extends TestCase {
    public function testGetTitle(): void {
        self::assertNull(
            Markdown::getTitle(
                <<<'MARKDOWN'
                ## Header A
                # Header B
                MARKDOWN,
            ),
        );
        self::assertNull(
            Markdown::getTitle(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header
                MARKDOWN,
            ),
        );
        self::assertNull(
            Markdown::getTitle(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ),
        );
        self::assertEquals(
            'Header',
            Markdown::getTitle(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ),
        );
    }

    public function testGetSummary(): void {
        self::assertNull(
            Markdown::getSummary(
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ),
        );
        self::assertNull(
            Markdown::getSummary(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ),
        );
        self::assertNull(
            Markdown::getSummary(
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf
                MARKDOWN,
            ),
        );
        self::assertEquals(
            'fsdfsdfsdf',
            Markdown::getSummary(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ),
        );
        self::assertEquals(
            <<<'TEXT'
            fsdfsdfsdf
            fsdfsdfsdf
            TEXT,
            Markdown::getSummary(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ),
        );
    }
}
