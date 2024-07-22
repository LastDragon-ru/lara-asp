<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(MarkdownDocument::class)]
final class MarkdownDocumentTest extends TestCase {
    public function testGetTitle(): void {
        self::assertNull(
            (new MarkdownDocument(
                <<<'MARKDOWN'
                ## Header A
                # Header B
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertNull(
            (new MarkdownDocument(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertNull(
            (new MarkdownDocument(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertEquals(
            'Header',
            (new MarkdownDocument(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
        self::assertEquals(
            'Header',
            (new MarkdownDocument(
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getTitle(),
        );
    }

    public function testGetSummary(): void {
        self::assertNull(
            (new MarkdownDocument(
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertNull(
            (new MarkdownDocument(
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertNull(
            (new MarkdownDocument(
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            'fsdfsdfsdf',
            (new MarkdownDocument(
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            <<<'TEXT'
            fsdfsdfsdf
            fsdfsdfsdf
            TEXT,
            (new MarkdownDocument(
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
        self::assertEquals(
            <<<'TEXT'
            fsdfsdfsdf
            fsdfsdfsdf
            TEXT,
            (new MarkdownDocument(
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ))
                ->getSummary(),
        );
    }
}
