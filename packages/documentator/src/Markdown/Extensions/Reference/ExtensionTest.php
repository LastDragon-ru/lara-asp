<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithMarkdown;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Extension::class)]
#[CoversClass(Parser::class)]
#[CoversClass(ParserStart::class)]
#[CoversClass(ParserContinue::class)]
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
                    new Extension(),
                ];
            }
        };

        $document   = $markdown->parse(self::getTestData()->content('~document.md'));
        $references = [];

        foreach ($document->node->getReferenceMap() as $label => $reference) {
            $references[$label] = $reference->getLabel();
        }

        self::assertEquals(
            [
                'simple:a'    => 'simple:a',
                'simple:b'    => 'simple:b',
                'simple:c'    => 'simple:c',
                'simple:d'    => 'simple:d',
                'simple:e'    => 'simple:e',
                'multiline:a' => 'multiline:a',
                'multiline:b' => 'multiline:b',
                'quote:a'     => 'quote:a',
                'quote:b'     => 'quote:b',
                'quote:c'     => 'quote:c',
                'quote:d'     => 'quote:d',
            ],
            $references,
        );

        $this->assertMarkdownDocumentEquals(
            self::getTestData()->content('~document.xml'),
            $document,
        );
    }
}
