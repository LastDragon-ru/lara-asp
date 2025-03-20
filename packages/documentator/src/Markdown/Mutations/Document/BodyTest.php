<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Body::class)]
final class BodyTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new Body());

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
                <<<'TEXT'
                ## Header A
                # Header B

                sdfsdfsdf

                TEXT,
                <<<'MARKDOWN'
                ## Header A
                # Header B

                sdfsdfsdf
                MARKDOWN,
            ],
            'Summary is the first node'  => [
                <<<'TEXT'
                # Header

                sdfsdfsdf

                TEXT,
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header

                sdfsdfsdf
                MARKDOWN,
            ],
            'Quote before #'             => [
                <<<'TEXT'
                > Not a paragraph

                fsdfsdfsdf

                text text text

                TEXT,
                <<<'MARKDOWN'
                # Header

                > Not a paragraph

                fsdfsdfsdf

                text text text
                MARKDOWN,
            ],
            'Empty #'                    => [
                <<<'TEXT'
                text text text

                text text text

                TEXT,
                <<<'MARKDOWN'
                #

                fsdfsdfsdf

                text text text

                text text text
                MARKDOWN,
            ],
            'Multiline summary'          => [
                <<<'TEXT'
                text text text

                text text text

                TEXT,
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                fsdfsdfsdf

                text text text

                text text text
                MARKDOWN,
            ],
            'Comments should be ignored' => [
                <<<'TEXT'
                <!-- Comment -->

                text text text

                TEXT,
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                summary

                <!-- Comment -->

                text text text
                MARKDOWN,
            ],
            'Splittable'                 => [
                <<<'TEXT'
                <!-- Comment -->

                text text text [link](https://example.com/)

                TEXT,
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                <!-- Comment -->

                summary

                <!-- Comment -->

                text text[^1] text [link][example]

                [example]: https://example.com/
                [^1]: Should be removed
                MARKDOWN,
            ],
        ];
    }
    // </editor-fold>
}
