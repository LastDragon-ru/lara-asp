<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Xml\MarkdownToXmlConverter;
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
            ->addBlockStartParser(new ParserStart(), 250)
            ->addRenderer(Block::class, new Renderer());

        $converter = new MarkdownToXmlConverter($environment);

        self::assertXmlStringEqualsXmlString(
            self::getTestData()->content('.xml'),
            (string) $converter->convert(
                self::getTestData()->content('.md'),
            ),
        );
    }
}
