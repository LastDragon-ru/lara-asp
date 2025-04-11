<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document as DocumentContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Markdown as MarkdownImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Exceptions\CodeLinkUnresolved;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithMarkdown;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Node\Node;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use XMLWriter;

use function mb_trim;
use function str_repeat;

/**
 * @internal
 */
#[CoversClass(Task::class)]
final class TaskTest extends TestCase {
    use WithProcessor;
    use WithMarkdown;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param string|Closure(): Exception $expected
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(Closure|string $expected, string $document): void {
        $path = (new FilePath(self::getTestData()->path($document)))->getNormalizedPath();
        $fs   = $this->getFileSystem($path->getDirectoryPath());
        $file = $fs->getFile($path);
        $task = $this->app()->make(Task::class);

        if ($expected instanceof Closure) {
            self::expectExceptionObject($expected());
        } else {
            $expected = self::getTestData()->content($expected);
        }

        $this->runProcessorTask($task, $fs, $file);

        self::assertEquals($expected, $file->as(Content::class)->content);
    }

    public function testParse(): void {
        $markdown = new MarkdownImpl();
        $renderer = $this->getMarkdownDocumentRenderer($markdown);
        $render   = static function (Node $node) use ($markdown, $renderer): string {
            $document = $markdown->parse('');

            $document->node->appendChild($node);

            return mb_trim(
                $renderer->render($document),
            );
        };

        $document = $markdown->parse(
            <<<'MARKDOWN'
            Text `💀App\Deprecated` text `App\ClassA` text [App\ClassB](https://example.com/)
            text [`\App\ClassC`](https://example.com/) text [`Class`](./class.php "App\ClassD")
            text [`Class`][class] text [`💀Class`][class].

            [//]: # (start: code-links)
            code-links section
            [//]: # (end: code-links)

            Text `💀App\Deprecated::method()` text `App\ClassA::$property` text
            text [`Class::Constant`](./class.php "App\ClassD::Constant") text
            text [`Class::method()`][method] text.

            [//]: # (start: code-links)
            [class]: ./class.php "App\ClassE"
            [method]: ./class.php "  App\ClassE::method()  "
            [//]: # (end: code-links)
            MARKDOWN,
        );
        $task     = new class(
            $this->app()->make(LinkFactory::class),
        ) extends Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function parse(DocumentContract $document): array {
                return parent::parse($document);
            }
        };
        $parsed   = $task->parse($document);
        $indent   = '    ';
        $xml      = new XmlWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString($indent);
        $xml->startDocument(encoding: 'UTF-8');
        $xml->startElement('expected');

        $xml->startElement('blocks');

        foreach ($parsed['blocks'] as $block) {
            $xml->startElement('node');
            $xml->writeRaw(Text::setPadding("\n{$render($block)}\n", 3, $indent));
            $xml->writeRaw(str_repeat($indent, 2));
            $xml->endElement();
        }

        $xml->endElement();

        $xml->startElement('links');

        foreach ($parsed['links'] as $link) {
            $xml->startElement('link');
            $xml->writeAttribute('class', $link->link::class);
            $xml->writeAttribute('target', (string) $link->link);
            $xml->writeAttribute('deprecated', $link->deprecated ? 'true' : 'false');

            foreach ($link->nodes as $node) {
                $xml->startElement('node');
                $xml->writeRaw(Text::setPadding("\n{$render($node)}\n", 4, $indent));
                $xml->writeRaw(str_repeat($indent, 3));
                $xml->endElement();
            }

            $xml->endElement();
        }

        $xml->endElement();

        $xml->endElement();
        $xml->endDocument();

        $actual   = $xml->outputMemory();
        $expected = self::getTestData()->content('Parse.xml');

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Closure(): Exception|string, string}>
     */
    public static function dataProviderInvoke(): array {
        return [
            'MultipleGenerated' => [
                'Invoke/InvokeMultipleGenerated~expected.md',
                'Invoke/InvokeMultipleGenerated.md',
            ],
            'NoGenerated'       => [
                'Invoke/InvokeNoGenerated~expected.md',
                'Invoke/InvokeNoGenerated.md',
            ],
            'Unknown'           => [
                static function (): Exception {
                    return new CodeLinkUnresolved(
                        [
                            '\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\Unknown',
                            '\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\Unknown::$property',
                            '\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\Unknown::Constant',
                            '\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\Unknown::method()',
                        ],
                    );
                },
                'Invoke/InvokeUnknown.md',
            ],
        ];
    }
    // </editor-fold>
}
