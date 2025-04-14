<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Parsers\BlockStartParserWrapper;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Parsers\InlineParserWrapper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithMarkdown;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Markdown::class)]
#[CoversClass(Locator::class)]
#[CoversClass(InlineParserWrapper::class)]
#[CoversClass(BlockStartParserWrapper::class)]
final class MarkdownTest extends TestCase {
    use WithMarkdown;

    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderParse')]
    public function testParse(string $expected, string $file): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse(self::getTestData()->content($file));
        $lines    = Lines::optional()->get($document->node);

        self::assertIsArray($lines);

        $this->assertMarkdownDocumentEquals(
            self::getTestData()->content($expected),
            $document,
        );
    }

    public function testRender(): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse(self::getTestData()->content('~document.md'));
        $actual   = $markdown->render($document);

        self::assertSame(
            self::getTestData()->content('~document.html'),
            $actual,
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string}>
     */
    public static function dataProviderParse(): array {
        return [
            'Basic'      => ['Basic.xml', 'Basic.md'],
            'Quotes'     => ['Quotes.xml', 'Quotes.md'],
            'Tables'     => ['Tables.xml', 'Tables.md'],
            'Headings'   => ['Headings.xml', 'Headings.md'],
            'Footnotes'  => ['Footnotes.xml', 'Footnotes.md'],
            'References' => ['References.xml', 'References.md'],
            'Html'       => ['Html.xml', 'Html.md'],
        ];
    }
    //</editor-fold>
}
