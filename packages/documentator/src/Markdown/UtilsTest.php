<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Utils::class)]
final class UtilsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testIsPathRelative(): void {
        // Nope
        self::assertFalse(Utils::isPathRelative('tel:+70000000000'));
        self::assertFalse(Utils::isPathRelative('urn:example.example'));
        self::assertFalse(Utils::isPathRelative('//example.com/'));
        self::assertFalse(Utils::isPathRelative('https://example.com/'));
        self::assertFalse(Utils::isPathRelative('mailto:mail@example.com'));
        self::assertFalse(Utils::isPathRelative('/path/to/file.md'));

        // Yep
        self::assertTrue(Utils::isPathRelative('.'));
        self::assertTrue(Utils::isPathRelative('..'));
        self::assertTrue(Utils::isPathRelative('path/to/file.md'));
        self::assertTrue(Utils::isPathRelative('./path/to/file.md'));
        self::assertTrue(Utils::isPathRelative('?query'));
        self::assertTrue(Utils::isPathRelative('#fragment'));
    }

    public function testIsPathToSelf(): void {
        $md = $this->app()->make(Markdown::class);
        $a  = $md->parse('', new FilePath('/path/to/a.md'));
        $b  = $md->parse('', new FilePath('/path/to/b.md'));

        self::assertTrue(Utils::isPathToSelf($a, '.'));
        self::assertTrue(Utils::isPathToSelf($a, '#fragment'));
        self::assertTrue(Utils::isPathToSelf($a, './a.md#fragment'));
        self::assertTrue(Utils::isPathToSelf($a, '?a=bc'));
        self::assertTrue(Utils::isPathToSelf($a, 'a.md'));
        self::assertTrue(Utils::isPathToSelf($a, './a.md'));
        self::assertTrue(Utils::isPathToSelf($a, '../to/a.md'));
        self::assertFalse(Utils::isPathToSelf($a, '/a.md'));
        self::assertFalse(Utils::isPathToSelf($a, '../a.md'));

        self::assertTrue(Utils::isPathToSelf($b, '/path/to/b.md'));
        self::assertFalse(Utils::isPathToSelf($b, 'a.md'));
        self::assertFalse(Utils::isPathToSelf($b, './a.md#fragment'));

        self::assertTrue(Utils::isPathToSelf($a, new FilePath('a.md')));
        self::assertTrue(Utils::isPathToSelf($a, new FilePath('../to/a.md')));
    }

    #[DataProvider('dataProviderGetTitle')]
    public function testGetTitle(?string $expected, string $document, ?FilePath $path): void {
        $document = $this->app()->make(Markdown::class)->parse($document, $path);
        $actual   = Utils::getTitle($document);

        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderGetLinkDestinationLocation')]
    public function testGetLinkDestinationLocation(Location $expected, string $definition): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($definition);
        $node     = (new Query())
            ->where(Query::type(Link::class))
            ->orWhere(Query::type(Image::class))
            ->findOne($document->node);

        self::assertTrue($node instanceof Link || $node instanceof Image);
        self::assertEquals($expected, Utils::getLinkDestinationLocation($document, $node));
    }

    #[DataProvider('dataProviderGetReferenceDestinationLocation')]
    public function testGetReferenceDestinationLocation(Location $expected, string $definition): void {
        $markdown = $this->app()->make(Markdown::class);
        $document = $markdown->parse($definition);
        $node     = (new Query())
            ->where(Query::type(ReferenceNode::class))
            ->findOne($document->node);

        self::assertTrue($node instanceof ReferenceNode);
        self::assertEquals($expected, Utils::getReferenceDestinationLocation($document, $node));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{?string, string, ?FilePath}>
     */
    public static function dataProviderGetTitle(): array {
        return [
            'no title, no path' => [
                null,
                <<<'MARKDOWN'
                text
                MARKDOWN,
                null,
            ],
            'no title, path'    => [
                'File name',
                <<<'MARKDOWN'
                text
                MARKDOWN,
                new FilePath('/path/to/file-name.md'),
            ],
            '# title, path'     => [
                'Title',
                <<<'MARKDOWN'
                # Title

                text
                MARKDOWN,
                new FilePath('/path/to/file-name.md'),
            ],
            '## title, path'    => [
                'File name',
                <<<'MARKDOWN'
                ## Title

                text
                MARKDOWN,
                new FilePath('/path/to/file-name.md'),
            ],
        ];
    }

    /**
     * @return array<string, array{Location, string}>
     */
    public static function dataProviderGetLinkDestinationLocation(): array {
        return [
            'link: without <> and without title'               => [
                new Location(1, 1, 7, 20),
                '[link](https://example.com/)',
            ],
            'link: without <>, without title and with spaces'  => [
                new Location(1, 1, 7, 24),
                '[link](  https://example.com/  )',
            ],
            'link: without <> and with title'                  => [
                new Location(1, 1, 7, 20),
                '[link](https://example.com/ "title")',
            ],
            'link: without <>, with title and with spaces'     => [
                new Location(1, 1, 7, 22),
                '[link](  https://example.com/  "title")',
            ],
            'link: without <> and with title escaping \''      => [
                new Location(1, 1, 7, 20),
                '[link](https://example.com/ "title with ( ) and with \' \'")',
            ],
            'link: without <> and with title escaping ()'      => [
                new Location(1, 1, 7, 20),
                '[link](https://example.com/ (title with \( \) and with \' \'))',
            ],
            'link: without <> and with title escaping "'       => [
                new Location(1, 1, 7, 20),
                '[link](https://example.com/ "title with ( ) and with \' \' and with \" \"")',
            ],
            'link: with <> and without title'                  => [
                new Location(1, 1, 7, 31),
                '[link](<https://example.com/ /\</path>)',
            ],
            'link: with <> and with title'                     => [
                new Location(1, 1, 7, 31),
                '[link](<https://example.com/ /\</path> "title")',
            ],
            'autolink'                                         => [
                new Location(1, 1, 0, 22),
                '<https://example.com/>',
            ],
            'image: without <> and without title'              => [
                new Location(1, 1, 9, 20),
                '![image](https://example.com/)',
            ],
            'image: without <>, without title and with spaces' => [
                new Location(1, 1, 9, 24),
                '![image](  https://example.com/  )',
            ],
            'image: without <> and with title'                 => [
                new Location(1, 1, 9, 20),
                '![image](https://example.com/ "title")',
            ],
            'image: without <>, with title and with spaces'    => [
                new Location(1, 1, 9, 22),
                '![image](  https://example.com/  "title")',
            ],
            'image: with <> and without title'                 => [
                new Location(1, 1, 9, 31),
                '![image](<https://example.com/ /\</path>)',
            ],
            'image: with <> and with title'                    => [
                new Location(1, 1, 9, 31),
                '![image](<https://example.com/ /\</path> "title")',
            ],
            'image: without <> and with title escaping \''     => [
                new Location(1, 1, 9, 20),
                '![image](https://example.com/ "title with ( ) and with \' \'")',
            ],
            'image: without <> and with title escaping ()'     => [
                new Location(1, 1, 9, 20),
                '![image](https://example.com/ (title with \( \) and with \' \'))',
            ],
            'image: without <> and with title escaping "'      => [
                new Location(1, 1, 9, 20),
                '![image](https://example.com/ "title with ( ) and with \' \' and with \" \"")',
            ],
        ];
    }

    /**
     * @return array<string, array{Location, string}>
     */
    public static function dataProviderGetReferenceDestinationLocation(): array {
        return [
            'one line, no <>, no title'                  => [
                new Location(1, 1, 8, 20),
                '[link]: https://example.com/',
            ],
            'one line, no <>, with title'                => [
                new Location(1, 1, 8, 20),
                '[link]: https://example.com/ "title"',
            ],
            'one line, with <>, no title'                => [
                new Location(1, 1, 8, 22),
                '[link]: <https://example.com/>',
            ],
            'one line, with <>, with title'              => [
                new Location(1, 1, 8, 22),
                '[link]: <https://example.com/> "title"',
            ],
            'multiline, no padding, no <>, no title'     => [
                new Location(2, 2, 0, 20),
                <<<'MARKDOWN'
                [link]:
                https://example.com/
                MARKDOWN,
            ],
            'multiline, padding, no <>, no title'        => [
                new Location(2, 2, 4, 20),
                <<<'MARKDOWN'
                [link]:
                    https://example.com/
                MARKDOWN,
            ],
            'multiline, no padding, no <>, with title'   => [
                new Location(2, 2, 0, 20),
                <<<'MARKDOWN'
                [link]:
                https://example.com/ "title"
                MARKDOWN,
            ],
            'multiline, padding, no <>, with title'      => [
                new Location(2, 2, 4, 20),
                <<<'MARKDOWN'
                [link]:
                    https://example.com/ "title"
                MARKDOWN,
            ],
            'multiline, no padding, with <>, with title' => [
                new Location(2, 2, 0, 22),
                <<<'MARKDOWN'
                [link]:
                <https://example.com/> 'title (with parens)'
                MARKDOWN,
            ],
            'multiline, padding, with <>, with title'    => [
                new Location(2, 2, 4, 22),
                <<<'MARKDOWN'
                [link]:
                    <https://example.com/>
                'title (with parens)'
                MARKDOWN,
            ],
        ];
    }
    // </editor-fold>
}
