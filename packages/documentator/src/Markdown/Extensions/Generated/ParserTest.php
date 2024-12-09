<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Extension;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Block as ReferenceBlock;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Renderer as ReferenceRenderer;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\RendererWrapper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Xml\XmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ParserStart::class)]
#[CoversClass(ParserContinue::class)]
final class ParserTest extends TestCase {
    public function testParse(): void {
        $converter   = new GithubFlavoredMarkdownConverter();
        $environment = $converter->getEnvironment()
            ->addExtension(new Extension())
            ->addRenderer(ReferenceBlock::class, new RendererWrapper(new ReferenceRenderer()))
            ->addRenderer(Block::class, new RendererWrapper(new Renderer()));

        $parser   = new MarkdownParser($environment);
        $document = $parser->parse(self::getTestData()->content('~document.md'));
        $renderer = new XmlRenderer($environment);

        self::assertXmlStringEqualsXmlString(
            self::getTestData()->content('~expected.xml'),
            $renderer->renderDocument($document)->getContent(),
        );
    }
}
