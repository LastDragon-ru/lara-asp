<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 * @deprecated 7.0.0
 */
#[CoversClass(Markdown::class)]
final class MarkdownTest extends TestCase {
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
        self::assertSame(
            'Header',
            Markdown::getTitle(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ),
        );
        self::assertSame(
            'Header',
            Markdown::getTitle(
                <<<'MARKDOWN'
                <!-- Comment -->

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
        self::assertSame(
            'fsdfsdfsdf',
            Markdown::getSummary(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ),
        );
        self::assertSame(
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
        self::assertSame(
            <<<'TEXT'
            fsdfsdfsdf
            fsdfsdfsdf
            TEXT,
            Markdown::getSummary(
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ),
        );
    }

    public function testSetPadding(): void {
        self::assertSame(
            <<<'TEXT'
                # Header

                fsdfsdfsdf
                fsdfsdfsdf

                * a
                  * a.a
                  * a.b
                * b
            TEXT,
            Markdown::setPadding(
                <<<'MARKDOWN'
                # Header

                fsdfsdfsdf
                fsdfsdfsdf

                * a
                  * a.a
                  * a.b
                * b
                MARKDOWN,
                4,
            ),
        );
        self::assertSame(
            <<<'MARKDOWN'
                    # Header

                    fsdfsdfsdf
                    fsdfsdfsdf

                * a
                  * a.a
                  * a.b

                * b
            MARKDOWN,
            Markdown::setPadding(
                <<<'MARKDOWN'
                    # Header

                    fsdfsdfsdf
                    fsdfsdfsdf

                * a
                  * a.a
                  * a.b

                * b
                MARKDOWN,
                4,
            ),
        );
    }
}
