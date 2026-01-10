<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\AstRestorer\Footnote;

use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\AstRestorer\Extension;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithMarkdown;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Extension::class)]
#[CoversClass(Listener::class)]
final class ExtensionTest extends TestCase {
    use WithMarkdown;

    public function testParse(): void {
        $markdown = new class() extends Markdown {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function extensions(): array {
                return [
                    new FootnoteExtension(),
                    new Extension(),
                ];
            }
        };

        $document = $markdown->parse(self::getTestData()->content('~document.md'));

        $this->assertMarkdownDocumentEquals(
            self::getTestData()->content('~document.xml'),
            $document,
        );
    }
}
