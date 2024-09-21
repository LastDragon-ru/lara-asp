<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extension;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated\Block as GeneratedNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated\Renderer as GeneratedNodeRenderer;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Renderer as ReferenceNodeRenderer;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\RendererWrapper;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Composer;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Exceptions\CodeLinkUnresolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassConstantLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassMethodLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassPropertyLink;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code as CodeNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\CodeRenderer as CodeNodeRenderer;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\LinkRenderer as LinkNodeRenderer;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use League\CommonMark\Xml\XmlRenderer;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;
use function array_walk_recursive;
use function dirname;
use function is_callable;
use function trim;

/**
 * @internal
 */
#[CoversClass(Task::class)]
final class TaskTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param string|Closure(static, Directory, File): Exception $expected
     */
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(Closure|string $expected, string $document): void {
        $path = Path::normalize(self::getTestData()->path($document));
        $file = new File($path, true);
        $root = new Directory(dirname($path), true);
        $task = $this->app()->make(Task::class);

        if (!is_callable($expected)) {
            $expected = self::getTestData()->content($expected);
        } else {
            self::expectExceptionObject($expected($this, $root, $file));
        }

        $actual = ProcessorHelper::runTask($task, $root, $file);

        self::assertTrue($actual);
        self::assertEquals($expected, $file->getContent());
    }

    public function testParse(): void {
        $converter   = new GithubFlavoredMarkdownConverter();
        $environment = $converter->getEnvironment()
            ->addExtension(new Extension())
            ->addRenderer(LinkNode::class, new RendererWrapper(new LinkNodeRenderer()))
            ->addRenderer(CodeNode::class, new RendererWrapper(new CodeNodeRenderer()))
            ->addRenderer(ReferenceNode::class, new RendererWrapper(new ReferenceNodeRenderer()))
            ->addRenderer(GeneratedNode::class, new RendererWrapper(new GeneratedNodeRenderer()));
        $renderer    = new XmlRenderer($environment);
        $render      = static function (Node $node) use ($renderer): string {
            $document = new DocumentNode();

            $document->appendChild($node);

            return trim((string) $renderer->renderDocument($document));
        };

        $document = new Document(
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
            [method]: ./class.php "App\ClassE::method()"
            [//]: # (end: code-links)
            MARKDOWN,
        );
        $comment  = $this->app()->make(PhpClassComment::class);
        $task     = new class(
            $this->app()->make(LinkFactory::class),
            $this->app()->make(Markdown::class),
            $this->app()->make(Composer::class),
            $comment,
        ) extends Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function parse(Document $document): array {
                return parent::parse($document);
            }
        };
        $actual   = $task->parse($document);

        array_walk_recursive($actual, static function (mixed &$item) use ($render): void {
            if ($item instanceof Node) {
                $item = $render($item);
            } elseif ($item instanceof LinkToken) {
                $item = [
                    'link'       => $item->link,
                    'deprecated' => $item->deprecated,
                    'nodes'      => array_map($render(...), $item->nodes),
                ];
            } else {
                // as is
            }
        });

        self::assertEquals(
            [
                'blocks' => [
                    <<<'XML'
                    <?xml version="1.0" encoding="UTF-8"?>
                    <document xmlns="http://commonmark.org/xml/1.0">
                        <generated id="code-links" startMarkerLocation="[{5,0,null}]" endMarkerLocation="[{7,0,null},{8,0,null}]" location="[{5,0,null},{6,0,null},{7,0,null},{8,0,null}]" blockPadding="0">
                            <paragraph>
                                <text>code-links section</text>
                            </paragraph>
                        </generated>
                    </document>
                    XML,
                    <<<'XML'
                    <?xml version="1.0" encoding="UTF-8"?>
                    <document xmlns="http://commonmark.org/xml/1.0">
                        <generated id="code-links" startMarkerLocation="[{13,0,null}]" endMarkerLocation="[{16,0,null}]" location="[{13,0,null},{14,0,null},{15,0,null},{16,0,null}]" blockPadding="0">
                            <reference label="class" destination="./class.php" title="App\ClassE" location="[{14,0,null}]" blockPadding="0" />
                            <reference label="method" destination="./class.php" title="App\ClassE::method()" location="[{15,0,null},{16,0,null}]" blockPadding="0" />
                        </generated>
                    </document>
                    XML,
                ],
                'links'  => [
                    [
                        'link'       => new ClassLink($comment, '\\App\\Deprecated'),
                        'deprecated' => true,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <code location="[{1,5,17}]" offset="1">💀App\Deprecated</code>
                            </document>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassLink($comment, '\\App\\ClassA'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <code location="[{1,28,12}]" offset="1">App\ClassA</code>
                            </document>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassLink($comment, '\\App\\ClassC'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <link destination="https://example.com/" title="" location="[{2,5,37}]" offset="15">
                                    <code location="[{2,6,13}]" offset="1">\App\ClassC</code>
                                </link>
                            </document>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassLink($comment, '\\App\\ClassD'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <link destination="./class.php" title="App\ClassD" location="[{2,48,35}]" offset="9">
                                    <code location="[{2,49,7}]" offset="1">Class</code>
                                </link>
                            </document>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassLink($comment, '\\App\\ClassE'),
                        'deprecated' => true,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <link destination="./class.php" title="App\ClassE" location="[{3,5,16}]" offset="9">
                                    <code location="[{3,6,7}]" offset="1">Class</code>
                                </link>
                            </document>
                            XML,
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <link destination="./class.php" title="App\ClassE" location="[{3,27,17}]" offset="10">
                                    <code location="[{3,28,8}]" offset="1">💀Class</code>
                                </link>
                            </document>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassMethodLink($comment, '\\App\\Deprecated', 'method'),
                        'deprecated' => true,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <code location="[{9,5,27}]" offset="1">💀App\Deprecated::method()</code>
                            </document>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassPropertyLink($comment, '\\App\\ClassA', 'property'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <code location="[{9,38,23}]" offset="1">App\ClassA::$property</code>
                            </document>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassConstantLink($comment, '\\App\\ClassD', 'Constant'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <link destination="./class.php" title="App\ClassD::Constant" location="[{10,5,55}]" offset="19">
                                    <code location="[{10,6,17}]" offset="1">Class::Constant</code>
                                </link>
                            </document>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassMethodLink($comment, '\\App\\ClassE', 'method'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <document xmlns="http://commonmark.org/xml/1.0">
                                <link destination="./class.php" title="App\ClassE::method()" location="[{11,5,27}]" offset="19">
                                    <code location="[{11,6,17}]" offset="1">Class::method()</code>
                                </link>
                            </document>
                            XML,
                        ],
                    ],
                ],
            ],
            $actual,
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Closure(static, Directory, File): Exception|string, string}>
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
                static function (self $test, Directory $root, File $file): Exception {
                    return new CodeLinkUnresolved(
                        $root,
                        $file,
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
