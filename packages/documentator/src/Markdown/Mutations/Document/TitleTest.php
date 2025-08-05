<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Title::class)]
#[CoversClass(TitleData::class)]
final class TitleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(string $expected, string $content): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($content);
        $actual   = (string) $document->mutate(new Title());

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
            'No #'                => [
                '',
                <<<'MARKDOWN'
                ## Header A
                # Header B
                MARKDOWN,
            ],
            'The # is not first'  => [
                '',
                <<<'MARKDOWN'
                fsdfsdfsdf

                # Header
                MARKDOWN,
            ],
            'The # is empty'      => [
                '',
                <<<'MARKDOWN'
                #

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Empty line before #' => [
                <<<'MARKDOWN'
                Header

                MARKDOWN,
                <<<'MARKDOWN'

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Comment before #'    => [
                <<<'MARKDOWN'
                Header

                MARKDOWN,
                <<<'MARKDOWN'
                <!-- Comment -->

                # Header

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Setext'              => [
                <<<'MARKDOWN'
                Header
                second line

                MARKDOWN,
                <<<'MARKDOWN'
                <!-- Comment -->

                Header
                second line
                =====

                fsdfsdfsdf
                MARKDOWN,
            ],
            'Splittable + Unlink' => [
                <<<'MARKDOWN'
                Header link

                MARKDOWN,
                <<<'MARKDOWN'
                # Header[^1] [link][example]

                [example]: https://example.com/
                [^1]: Should be removed
                MARKDOWN,
            ],
            'Inside Quote'        => [
                '',
                <<<'MARKDOWN'
                > # Header
                MARKDOWN,
            ],
        ];
    }
    // </editor-fold>
}
