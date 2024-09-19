<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Composer\Autoload;
use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJson;
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
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Exceptions\CodeLinkUnresolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassConstantLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassMethodLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassPropertyLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\Traits\ClassTitle;
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
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;
use function array_walk_recursive;
use function dirname;
use function trim;

/**
 * @internal
 */
#[CoversClass(Task::class)]
final class TaskTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderInvoke')]
    public function testInvoke(Exception|string $expected, string $document): void {
        $path = Path::normalize(self::getTestData()->path($document));
        $file = new File($path, true);
        $root = new Directory(dirname($path), true);
        $task = $this->app()->make(Task::class);

        if (!($expected instanceof Exception)) {
            $expected = self::getTestData()->content($expected);
        } else {
            self::expectExceptionObject($expected);
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
        $task     = new class(
            $this->app()->make(LinkFactory::class),
            $this->app()->make(Markdown::class),
            $this->app()->make(Composer::class),
            $this->app()->make(PhpClassComment::class),
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
                        'link'       => new ClassLink('\\App\\Deprecated'),
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
                        'link'       => new ClassLink('\\App\\ClassA'),
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
                        'link'       => new ClassLink('\\App\\ClassC'),
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
                        'link'       => new ClassLink('\\App\\ClassD'),
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
                        'link'       => new ClassLink('\\App\\ClassE'),
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
                        'link'       => new ClassMethodLink('\\App\\Deprecated', 'method'),
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
                        'link'       => new ClassPropertyLink('\\App\\ClassA', 'property'),
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
                        'link'       => new ClassConstantLink('\\App\\ClassD', 'Constant'),
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
                        'link'       => new ClassMethodLink('\\App\\ClassE', 'method'),
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

    public function testGetLinkTokenPaths(): void {
        $composer = new ComposerJson(
            autoload   : new Autoload([
                '\\A\\B\\C\\' => ['a/b/c/'],
                '\\A\\B\\'    => 'a/b/',
            ]),
            autoloadDev: new Autoload([
                '\\C\\' => ['c/a', 'c/b', ''],
            ]),
        );
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $node     = Mockery::mock(CodeNode::class);
        $aToken   = new LinkToken(new ClassLink('\\A\\B\\C\\Class'), false, [$node]);
        $bToken   = new LinkToken(new ClassConstantLink('\\A\\B\\Class', 'Constant'), false, [$node]);
        $cToken   = new LinkToken(new ClassPropertyLink('\\C\\Class', 'property'), false, [$node]);
        $dToken   = new LinkToken(new ClassMethodLink('\\C\\D\\Class', 'method'), false, [$node]);
        $eToken   = new LinkToken(new ClassLink('\\Class'), false, [$node]);
        $fToken   = new LinkToken(
            new class() implements Link {
                use ClassTitle;

                #[Override]
                public function __toString(): string {
                    return 'link';
                }
            },
            false,
            [$node],
        );
        $task     = new class () extends Task {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getLinkTokenPaths(
                Directory $root,
                File $file,
                LinkToken $token,
                ComposerJson $composer,
            ): ?array {
                return parent::getLinkTokenPaths($root, $file, $token, $composer);
            }
        };

        self::assertEquals(
            [
                'a/b/c/Class.php',
                'a/b/C/Class.php',
            ],
            $task->getLinkTokenPaths($root, $file, $aToken, $composer),
        );
        self::assertEquals(
            [
                'a/b/Class.php',
            ],
            $task->getLinkTokenPaths($root, $file, $bToken, $composer),
        );
        self::assertEquals(
            [
                'a/b/Class.php',
            ],
            $task->getLinkTokenPaths($root, $file, $bToken, $composer),
        );
        self::assertEquals(
            [
                'c/a/Class.php',
                'c/b/Class.php',
                'Class.php',
            ],
            $task->getLinkTokenPaths($root, $file, $cToken, $composer),
        );
        self::assertEquals(
            [
                'c/a/D/Class.php',
                'c/b/D/Class.php',
                'D/Class.php',
            ],
            $task->getLinkTokenPaths($root, $file, $dToken, $composer),
        );
        self::assertNull(
            $task->getLinkTokenPaths($root, $file, $eToken, $composer),
        );
        self::assertEquals(
            [
                // empty
            ],
            $task->getLinkTokenPaths($root, $file, $fToken, $composer),
        );
    }

    /**
     * @param Closure(static): LinkToken $tokenFactory
     * @param Closure(static): File      $sourceFactory
     */
    #[DataProvider('dataProviderGetLinkTokenTarget')]
    public function testGetLinkTokenTarget(?LinkTarget $expected, Closure $tokenFactory, Closure $sourceFactory): void {
        $task   = new class(
            $this->app()->make(LinkFactory::class),
            $this->app()->make(Markdown::class),
            $this->app()->make(Composer::class),
            $this->app()->make(PhpClassComment::class),
        ) extends Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getLinkTokenTarget(
                Directory $root,
                File $file,
                LinkToken $token,
                File $source,
            ): ?LinkTarget {
                return parent::getLinkTokenTarget($root, $file, $token, $source);
            }
        };
        $path   = Path::normalize(self::getTestData()->path('GetLinkTokenTarget/TestClass.php'));
        $root   = new Directory(dirname($path), false);
        $file   = new File($path, false);
        $token  = $tokenFactory($this);
        $source = $sourceFactory($this);
        $actual = $task->getLinkTokenTarget($root, $file, $token, $source);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|string, string}>
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
                new CodeLinkUnresolved([
                    '\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\Unknown',
                    '\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\Unknown::$property',
                    '\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\Unknown::Constant',
                    '\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\TaskTest\Invoke\Unknown::method()',
                ]),
                'Invoke/InvokeUnknown.md',
            ],
        ];
    }

    /**
     * @return array<string, array{?LinkTarget, Closure(static): LinkToken, Closure(static): File}>
     */
    public static function dataProviderGetLinkTokenTarget(): array {
        return [
            'not php'                              => [
                null,
                static function (): LinkToken {
                    return new LinkToken(
                        new class() implements Link {
                            use ClassTitle;

                            #[Override]
                            public function __toString(): string {
                                return 'link';
                            }
                        },
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (): File {
                    $file = Mockery::mock(File::class);
                    $file
                        ->shouldReceive('getMetadata')
                        ->once()
                        ->andReturn(null);

                    return $file;
                },
            ],
            'class'                                => [
                new LinkTarget('TestClass.php', false, null, null),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class unknown'                        => [
                null,
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClassUnknown',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class deprecated'                     => [
                new LinkTarget('TestClassDeprecated.php', true, null, null),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClassDeprecated',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClassDeprecated.php')),
                        false,
                    );
                },
            ],
            'class deprecated / constant'          => [
                new LinkTarget(
                    'TestClassDeprecated.php',
                    true,
                    10,
                    10,
                ),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassConstantLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClassDeprecated',
                            'Constant',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClassDeprecated.php')),
                        false,
                    );
                },
            ],
            'class deprecated / property'          => [
                new LinkTarget(
                    'TestClassDeprecated.php',
                    true,
                    12,
                    12,
                ),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassPropertyLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClassDeprecated',
                            'property',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClassDeprecated.php')),
                        false,
                    );
                },
            ],
            'class deprecated / property promoted' => [
                new LinkTarget(
                    'TestClassDeprecated.php',
                    true,
                    15,
                    15,
                ),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassPropertyLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClassDeprecated',
                            'promoted',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClassDeprecated.php')),
                        false,
                    );
                },
            ],
            'class deprecated / method'            => [
                new LinkTarget(
                    'TestClassDeprecated.php',
                    true,
                    20,
                    22,
                ),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassMethodLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClassDeprecated',
                            'method',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClassDeprecated.php')),
                        false,
                    );
                },
            ],
            'class / constant'                     => [
                new LinkTarget('TestClass.php', false, 9, 9),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassConstantLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'Constant',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / constant unknown'             => [
                null,
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassConstantLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'Unknown',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / constant deprecated'          => [
                new LinkTarget('TestClass.php', true, 10, 13),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassConstantLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'ConstantDeprecated',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / property'                     => [
                new LinkTarget('TestClass.php', false, 15, 15),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassPropertyLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'property',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / property promoted'            => [
                new LinkTarget('TestClass.php', false, 23, 23),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassPropertyLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'promoted',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / property unknown'             => [
                null,
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassPropertyLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'unknown',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / property deprecated'          => [
                new LinkTarget('TestClass.php', true, 17, 20),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassPropertyLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'propertyDeprecated',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / property promoted deprecated' => [
                new LinkTarget('TestClass.php', true, 24, 27),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassPropertyLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'promotedDeprecated',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / method'                       => [
                new LinkTarget('TestClass.php', false, 32, 34),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassMethodLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'method',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / method unknown'               => [
                null,
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassMethodLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'unknown',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
            'class / method deprecated'            => [
                new LinkTarget('TestClass.php', true, 36, 41),
                static function (): LinkToken {
                    return new LinkToken(
                        new ClassMethodLink(
                            '\\LastDragon_ru\\LaraASP\\Documentator\\Processor\\Tasks\\CodeLinks\\TaskTest\\GetLinkTokenTarget\\TestClass',
                            'methodDeprecated',
                        ),
                        false,
                        [
                            Mockery::mock(CodeNode::class),
                        ],
                    );
                },
                static function (self $test): File {
                    return new File(
                        Path::normalize($test::getTestData()->path('GetLinkTokenTarget/TestClass.php')),
                        false,
                    );
                },
            ],
        ];
    }
    // </editor-fold>
}
