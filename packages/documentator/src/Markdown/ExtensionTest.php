<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Renderer;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Query;
use League\CommonMark\Parser\MarkdownParser;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Extension::class)]
final class ExtensionTest extends TestCase {
    public function testExtension(): void {
        $converter   = new GithubFlavoredMarkdownConverter();
        $environment = $converter->getEnvironment()
            ->addExtension(new Extension())
            ->addRenderer(Block::class, new Renderer());

        $parser   = new MarkdownParser($environment);
        $markdown = "# Header\nParagraph [link](https://example.com/).";
        $document = $parser->parse($markdown);
        $lines    = $document->data->get(Lines::class, null);
        $link     = (new Query())->where(Query::type(Link::class))->findOne($document);

        self::assertInstanceOf(Lines::class, $lines);
        self::assertCount(2, $lines->get());
        self::assertNotNull($link);
        self::assertEquals(
            [
                new Coordinate(2, 10, 28),
            ],
            iterator_to_array(
                Cast::toIterable($link->data->get(Location::class, null) ?? []),
            ),
        );
    }
}
