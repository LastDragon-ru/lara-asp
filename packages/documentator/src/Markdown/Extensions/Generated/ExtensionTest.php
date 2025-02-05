<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\DocumentRenderer;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Extension::class)]
#[CoversClass(ParserStart::class)]
#[CoversClass(ParserContinue::class)]
final class ExtensionTest extends TestCase {
    public function testParse(): void {
        $markdown = new class() extends Markdown {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function extensions(): array {
                return [
                    new Extension(),
                ];
            }
        };

        $renderer = $this->app()->make(DocumentRenderer::class);
        $document = $markdown->parse(self::getTestData()->content('~document.md'));

        self::assertSame(
            self::getTestData()->content('~document.xml'),
            $renderer->render($document),
        );
    }
}
