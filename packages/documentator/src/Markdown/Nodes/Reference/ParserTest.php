<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Extension;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Xml\XmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Parser::class)]
#[CoversClass(ParserStart::class)]
#[CoversClass(ParserContinue::class)]
#[CoversClass(Renderer::class)]
final class ParserTest extends TestCase {
    public function testParse(): void {
        $converter   = new GithubFlavoredMarkdownConverter();
        $environment = $converter->getEnvironment()
            ->addExtension(new Extension())
            ->addRenderer(Block::class, new Renderer());

        $parser     = new MarkdownParser($environment);
        $document   = $parser->parse(self::getTestData()->content('~document.md'));
        $renderer   = new XmlRenderer($environment);
        $references = [];

        foreach ($document->getReferenceMap() as $label => $reference) {
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
        self::assertXmlStringEqualsXmlString(
            self::getTestData()->content('~expected.xml'),
            $renderer->renderDocument($document)->getContent(),
        );
    }
}
