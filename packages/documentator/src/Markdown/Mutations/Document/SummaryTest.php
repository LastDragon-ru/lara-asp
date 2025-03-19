<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Summary::class)]
#[CoversClass(SummaryData::class)]
final class SummaryTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new Summary());

        self::assertSame($expected, $actual);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'The # is not first'         => [
                '',
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ],
            'Summary is the first node'  => [
                <<<'MARKDOWN'
                fsdfsdfsdf

                MARKDOWN,
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ],
            'Quote before #'             => [
                '',
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Empty #'                    => [
                <<<'MARKDOWN'
                fsdfsdfsdf

                MARKDOWN,
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Multiline'                  => [
                <<<'TEXT'
                fsdfsdfsdf
                fsdfsdfsdf

                TEXT,
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ],
            'Comments should be ignored' => [
                <<<'TEXT'
                fsdfsdfsdf
                fsdfsdfsdf

                TEXT,
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                fsdfsdfsdf
                fsdfsdfsdf
                MARKDOWN,
            ],
            'Splittable'                 => [
                <<<'TEXT'
                fsdfsdfsdf [link](https://example.com/)
                fsdfsdfsdf

                TEXT,
                <<<'MARKDOWN'
                # Header

                fsdfsdfsdf [link][example]
                fsdfsdfsdf[^1]

                [example]: https://example.com/
                [^1]: Should be removed
                MARKDOWN,
            ],
        ];
    }
    // </editor-fold>
}
