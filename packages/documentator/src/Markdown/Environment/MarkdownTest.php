<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\DocumentRenderer;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Markdown::class)]
final class MarkdownTest extends TestCase {
    public function testParse(): void {
        $renderer = $this->app()->make(DocumentRenderer::class);
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse(self::getTestData()->content('~document.md'));
        $lines    = Lines::optional()->get($document->node);

        self::assertIsArray($lines);
        self::assertSame(
            self::getTestData()->content('~document.xml'),
            $renderer->render($document),
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
}
