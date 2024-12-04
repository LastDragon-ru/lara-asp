<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Coordinate;
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
            ->addExtension(new Extension());

        $parser   = new MarkdownParser($environment);
        $markdown = "# Header\nParagraph [link](https://example.com/).";
        $document = $parser->parse($markdown);
        $lines    = Lines::get($document);
        $link     = (new Query())->where(Query::type(Link::class))->findOne($document);

        self::assertIsArray($lines);
        self::assertCount(2, $lines);
        self::assertNotNull($link);
        self::assertEquals(
            [
                new Coordinate(2, 10, 28),
            ],
            iterator_to_array(
                Location::get($link) ?? [],
            ),
        );
    }
}
