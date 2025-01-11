<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Markdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\DocumentRenderer;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Environment\Environment;
use League\CommonMark\GithubFlavoredMarkdownConverter;
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
    public function testParse(): void {
        $markdown = new class() extends Markdown {
            #[Override]
            protected function initialize(): Environment {
                $converter   = new GithubFlavoredMarkdownConverter();
                $environment = $converter->getEnvironment()
                    ->addExtension(new Extension());

                return $environment;
            }
        };

        $renderer   = $this->app()->make(DocumentRenderer::class);
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
        self::assertSame(
            self::getTestData()->content('~document.xml'),
            $renderer->render($document),
        );
    }
}
