<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Markdown as MarkdownImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Exceptions\CodeLinkUnresolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassConstantLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassMethodLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassPropertyLink;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\DocumentRenderer;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;
use function array_walk_recursive;
use function mb_trim;

/**
 * @internal
 */
#[CoversClass(Task::class)]
final class TaskTest extends TestCase {
    use WithProcessor;

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

        $actual = $this->getProcessorResult($fs, ($task)($file));

        self::assertTrue($actual);
        self::assertEquals($expected, $file->getMetadata(Content::class));
    }

    public function testParse(): void {
        $markdown = new class() extends MarkdownImpl {
            public function getEnvironment(): EnvironmentInterface {
                return $this->environment;
            }
        };
        $renderer = $this->app()->make(DocumentRenderer::class);
        $render   = static function (Node $node) use ($markdown, $renderer): string {
            return mb_trim(
                $renderer->render(
                    new class ($markdown, $node) extends Document {
                        public function __construct(MarkdownContract $markdown, Node $node) {
                            $document = new DocumentNode();

                            $document->appendChild($node);

                            parent::__construct($markdown, $document, null);
                        }
                    },
                ),
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
                    <markdown>
                        <node name="document">
                            <attributes>
                                <attribute name="xmlns">
                                    <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                </attribute>
                            </attributes>
                            <data>
                                <item key="attributes">
                                    <array length="0"/>
                                </item>
                            </data>
                            <node class="LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Node">
                                <data>
                                    <item key="attributes">
                                        <array length="0"/>
                                    </item>
                                    <item key="lara-asp-documentator">
                                        <array length="3">
                                            <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding">
                                                <int>0</int>
                                            </item>
                                            <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\StartMarkerLocation">
                                                <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                    <property name="endLine">
                                                        <int>5</int>
                                                    </property>
                                                    <property name="internalPadding">
                                                        <null/>
                                                    </property>
                                                    <property name="length">
                                                        <null/>
                                                    </property>
                                                    <property name="offset">
                                                        <int>0</int>
                                                    </property>
                                                    <property name="startLine">
                                                        <int>5</int>
                                                    </property>
                                                    <property name="startLinePadding">
                                                        <int>0</int>
                                                    </property>
                                                </object>
                                            </item>
                                            <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\EndMarkerLocation">
                                                <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                    <property name="endLine">
                                                        <int>8</int>
                                                    </property>
                                                    <property name="internalPadding">
                                                        <null/>
                                                    </property>
                                                    <property name="length">
                                                        <null/>
                                                    </property>
                                                    <property name="offset">
                                                        <int>0</int>
                                                    </property>
                                                    <property name="startLine">
                                                        <int>7</int>
                                                    </property>
                                                    <property name="startLinePadding">
                                                        <int>0</int>
                                                    </property>
                                                </object>
                                            </item>
                                        </array>
                                    </item>
                                </data>
                                <node name="paragraph">
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="text">
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                        </data>
                                        <string><![CDATA[code-links section]]></string>
                                    </node>
                                </node>
                            </node>
                        </node>
                    </markdown>
                    XML,
                    <<<'XML'
                    <?xml version="1.0" encoding="UTF-8"?>
                    <markdown>
                        <node name="document">
                            <attributes>
                                <attribute name="xmlns">
                                    <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                </attribute>
                            </attributes>
                            <data>
                                <item key="attributes">
                                    <array length="0"/>
                                </item>
                            </data>
                            <node class="LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Node">
                                <data>
                                    <item key="attributes">
                                        <array length="0"/>
                                    </item>
                                    <item key="lara-asp-documentator">
                                        <array length="3">
                                            <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding">
                                                <int>0</int>
                                            </item>
                                            <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\StartMarkerLocation">
                                                <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                    <property name="endLine">
                                                        <int>13</int>
                                                    </property>
                                                    <property name="internalPadding">
                                                        <null/>
                                                    </property>
                                                    <property name="length">
                                                        <null/>
                                                    </property>
                                                    <property name="offset">
                                                        <int>0</int>
                                                    </property>
                                                    <property name="startLine">
                                                        <int>13</int>
                                                    </property>
                                                    <property name="startLinePadding">
                                                        <int>0</int>
                                                    </property>
                                                </object>
                                            </item>
                                            <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\EndMarkerLocation">
                                                <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                    <property name="endLine">
                                                        <int>16</int>
                                                    </property>
                                                    <property name="internalPadding">
                                                        <null/>
                                                    </property>
                                                    <property name="length">
                                                        <null/>
                                                    </property>
                                                    <property name="offset">
                                                        <int>0</int>
                                                    </property>
                                                    <property name="startLine">
                                                        <int>16</int>
                                                    </property>
                                                    <property name="startLinePadding">
                                                        <int>0</int>
                                                    </property>
                                                </object>
                                            </item>
                                        </array>
                                    </item>
                                </data>
                                <node class="LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node">
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                        <item key="lara-asp-documentator">
                                            <array length="1">
                                                <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding">
                                                    <int>0</int>
                                                </item>
                                            </array>
                                        </item>
                                    </data>
                                </node>
                                <node class="LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node">
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                        <item key="lara-asp-documentator">
                                            <array length="1">
                                                <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding">
                                                    <int>0</int>
                                                </item>
                                            </array>
                                        </item>
                                    </data>
                                </node>
                            </node>
                        </node>
                    </markdown>
                    XML,
                ],
                'links'  => [
                    [
                        'link'       => new ClassLink('\\App\\Deprecated'),
                        'deprecated' => true,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="code">
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>1</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>17</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>5</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>1</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>1</int>
                                                    </item>
                                                </array>
                                            </item>
                                        </data>
                                        <string><![CDATA[💀App\Deprecated]]></string>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassLink('\\App\\ClassA'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="code">
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>1</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>12</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>28</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>1</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>1</int>
                                                    </item>
                                                </array>
                                            </item>
                                        </data>
                                        <string><![CDATA[App\ClassA]]></string>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassLink('\\App\\ClassC'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="link">
                                        <attributes>
                                            <attribute name="destination">
                                                <string><![CDATA[https://example.com/]]></string>
                                            </attribute>
                                            <attribute name="title">
                                                <string><![CDATA[]]></string>
                                            </attribute>
                                        </attributes>
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>2</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>37</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>5</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>2</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>15</int>
                                                    </item>
                                                </array>
                                            </item>
                                        </data>
                                        <node name="code">
                                            <data>
                                                <item key="attributes">
                                                    <array length="0"/>
                                                </item>
                                                <item key="lara-asp-documentator">
                                                    <array length="2">
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                            <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                                <property name="endLine">
                                                                    <int>2</int>
                                                                </property>
                                                                <property name="internalPadding">
                                                                    <null/>
                                                                </property>
                                                                <property name="length">
                                                                    <int>13</int>
                                                                </property>
                                                                <property name="offset">
                                                                    <int>6</int>
                                                                </property>
                                                                <property name="startLine">
                                                                    <int>2</int>
                                                                </property>
                                                                <property name="startLinePadding">
                                                                    <int>0</int>
                                                                </property>
                                                            </object>
                                                        </item>
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                            <int>1</int>
                                                        </item>
                                                    </array>
                                                </item>
                                            </data>
                                            <string><![CDATA[\App\ClassC]]></string>
                                        </node>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassLink('\\App\\ClassD'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="link">
                                        <attributes>
                                            <attribute name="destination">
                                                <string><![CDATA[./class.php]]></string>
                                            </attribute>
                                            <attribute name="title">
                                                <string><![CDATA[App\ClassD]]></string>
                                            </attribute>
                                        </attributes>
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>2</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>35</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>48</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>2</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>9</int>
                                                    </item>
                                                </array>
                                            </item>
                                        </data>
                                        <node name="code">
                                            <data>
                                                <item key="attributes">
                                                    <array length="0"/>
                                                </item>
                                                <item key="lara-asp-documentator">
                                                    <array length="2">
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                            <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                                <property name="endLine">
                                                                    <int>2</int>
                                                                </property>
                                                                <property name="internalPadding">
                                                                    <null/>
                                                                </property>
                                                                <property name="length">
                                                                    <int>7</int>
                                                                </property>
                                                                <property name="offset">
                                                                    <int>49</int>
                                                                </property>
                                                                <property name="startLine">
                                                                    <int>2</int>
                                                                </property>
                                                                <property name="startLinePadding">
                                                                    <int>0</int>
                                                                </property>
                                                            </object>
                                                        </item>
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                            <int>1</int>
                                                        </item>
                                                    </array>
                                                </item>
                                            </data>
                                            <string><![CDATA[Class]]></string>
                                        </node>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassLink('\\App\\ClassE'),
                        'deprecated' => true,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="link">
                                        <attributes>
                                            <attribute name="destination">
                                                <string><![CDATA[./class.php]]></string>
                                            </attribute>
                                            <attribute name="title">
                                                <string><![CDATA[App\ClassE]]></string>
                                            </attribute>
                                        </attributes>
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>3</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>16</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>5</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>3</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>9</int>
                                                    </item>
                                                </array>
                                            </item>
                                            <item key="reference">
                                                <object class="League\CommonMark\Reference\Reference">
                                                    <property name="destination">
                                                        <string><![CDATA[./class.php]]></string>
                                                    </property>
                                                    <property name="label">
                                                        <string><![CDATA[class]]></string>
                                                    </property>
                                                    <property name="title">
                                                        <string><![CDATA[App\ClassE]]></string>
                                                    </property>
                                                </object>
                                            </item>
                                        </data>
                                        <node name="code">
                                            <data>
                                                <item key="attributes">
                                                    <array length="0"/>
                                                </item>
                                                <item key="lara-asp-documentator">
                                                    <array length="2">
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                            <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                                <property name="endLine">
                                                                    <int>3</int>
                                                                </property>
                                                                <property name="internalPadding">
                                                                    <null/>
                                                                </property>
                                                                <property name="length">
                                                                    <int>7</int>
                                                                </property>
                                                                <property name="offset">
                                                                    <int>6</int>
                                                                </property>
                                                                <property name="startLine">
                                                                    <int>3</int>
                                                                </property>
                                                                <property name="startLinePadding">
                                                                    <int>0</int>
                                                                </property>
                                                            </object>
                                                        </item>
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                            <int>1</int>
                                                        </item>
                                                    </array>
                                                </item>
                                            </data>
                                            <string><![CDATA[Class]]></string>
                                        </node>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="link">
                                        <attributes>
                                            <attribute name="destination">
                                                <string><![CDATA[./class.php]]></string>
                                            </attribute>
                                            <attribute name="title">
                                                <string><![CDATA[App\ClassE]]></string>
                                            </attribute>
                                        </attributes>
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>3</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>17</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>27</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>3</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>10</int>
                                                    </item>
                                                </array>
                                            </item>
                                            <item key="reference">
                                                <object class="League\CommonMark\Reference\Reference">
                                                    <property name="destination">
                                                        <string><![CDATA[./class.php]]></string>
                                                    </property>
                                                    <property name="label">
                                                        <string><![CDATA[class]]></string>
                                                    </property>
                                                    <property name="title">
                                                        <string><![CDATA[App\ClassE]]></string>
                                                    </property>
                                                </object>
                                            </item>
                                        </data>
                                        <node name="code">
                                            <data>
                                                <item key="attributes">
                                                    <array length="0"/>
                                                </item>
                                                <item key="lara-asp-documentator">
                                                    <array length="2">
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                            <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                                <property name="endLine">
                                                                    <int>3</int>
                                                                </property>
                                                                <property name="internalPadding">
                                                                    <null/>
                                                                </property>
                                                                <property name="length">
                                                                    <int>8</int>
                                                                </property>
                                                                <property name="offset">
                                                                    <int>28</int>
                                                                </property>
                                                                <property name="startLine">
                                                                    <int>3</int>
                                                                </property>
                                                                <property name="startLinePadding">
                                                                    <int>0</int>
                                                                </property>
                                                            </object>
                                                        </item>
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                            <int>1</int>
                                                        </item>
                                                    </array>
                                                </item>
                                            </data>
                                            <string><![CDATA[💀Class]]></string>
                                        </node>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassMethodLink('\\App\\Deprecated', 'method'),
                        'deprecated' => true,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="code">
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>9</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>27</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>5</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>9</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>1</int>
                                                    </item>
                                                </array>
                                            </item>
                                        </data>
                                        <string><![CDATA[💀App\Deprecated::method()]]></string>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassPropertyLink('\\App\\ClassA', 'property'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="code">
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>9</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>23</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>38</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>9</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>1</int>
                                                    </item>
                                                </array>
                                            </item>
                                        </data>
                                        <string><![CDATA[App\ClassA::$property]]></string>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassConstantLink('\\App\\ClassD', 'Constant'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="link">
                                        <attributes>
                                            <attribute name="destination">
                                                <string><![CDATA[./class.php]]></string>
                                            </attribute>
                                            <attribute name="title">
                                                <string><![CDATA[App\ClassD::Constant]]></string>
                                            </attribute>
                                        </attributes>
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>10</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>55</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>5</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>10</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>19</int>
                                                    </item>
                                                </array>
                                            </item>
                                        </data>
                                        <node name="code">
                                            <data>
                                                <item key="attributes">
                                                    <array length="0"/>
                                                </item>
                                                <item key="lara-asp-documentator">
                                                    <array length="2">
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                            <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                                <property name="endLine">
                                                                    <int>10</int>
                                                                </property>
                                                                <property name="internalPadding">
                                                                    <null/>
                                                                </property>
                                                                <property name="length">
                                                                    <int>17</int>
                                                                </property>
                                                                <property name="offset">
                                                                    <int>6</int>
                                                                </property>
                                                                <property name="startLine">
                                                                    <int>10</int>
                                                                </property>
                                                                <property name="startLinePadding">
                                                                    <int>0</int>
                                                                </property>
                                                            </object>
                                                        </item>
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                            <int>1</int>
                                                        </item>
                                                    </array>
                                                </item>
                                            </data>
                                            <string><![CDATA[Class::Constant]]></string>
                                        </node>
                                    </node>
                                </node>
                            </markdown>
                            XML,
                        ],
                    ],
                    [
                        'link'       => new ClassMethodLink('\\App\\ClassE', 'method'),
                        'deprecated' => false,
                        'nodes'      => [
                            <<<'XML'
                            <?xml version="1.0" encoding="UTF-8"?>
                            <markdown>
                                <node name="document">
                                    <attributes>
                                        <attribute name="xmlns">
                                            <string><![CDATA[http://commonmark.org/xml/1.0]]></string>
                                        </attribute>
                                    </attributes>
                                    <data>
                                        <item key="attributes">
                                            <array length="0"/>
                                        </item>
                                    </data>
                                    <node name="link">
                                        <attributes>
                                            <attribute name="destination">
                                                <string><![CDATA[./class.php]]></string>
                                            </attribute>
                                            <attribute name="title">
                                                <string><![CDATA[  App\ClassE::method()  ]]></string>
                                            </attribute>
                                        </attributes>
                                        <data>
                                            <item key="attributes">
                                                <array length="0"/>
                                            </item>
                                            <item key="lara-asp-documentator">
                                                <array length="2">
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                        <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                            <property name="endLine">
                                                                <int>11</int>
                                                            </property>
                                                            <property name="internalPadding">
                                                                <null/>
                                                            </property>
                                                            <property name="length">
                                                                <int>27</int>
                                                            </property>
                                                            <property name="offset">
                                                                <int>5</int>
                                                            </property>
                                                            <property name="startLine">
                                                                <int>11</int>
                                                            </property>
                                                            <property name="startLinePadding">
                                                                <int>0</int>
                                                            </property>
                                                        </object>
                                                    </item>
                                                    <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                        <int>19</int>
                                                    </item>
                                                </array>
                                            </item>
                                            <item key="reference">
                                                <object class="League\CommonMark\Reference\Reference">
                                                    <property name="destination">
                                                        <string><![CDATA[./class.php]]></string>
                                                    </property>
                                                    <property name="label">
                                                        <string><![CDATA[method]]></string>
                                                    </property>
                                                    <property name="title">
                                                        <string><![CDATA[  App\ClassE::method()  ]]></string>
                                                    </property>
                                                </object>
                                            </item>
                                        </data>
                                        <node name="code">
                                            <data>
                                                <item key="attributes">
                                                    <array length="0"/>
                                                </item>
                                                <item key="lara-asp-documentator">
                                                    <array length="2">
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location">
                                                            <object class="LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location">
                                                                <property name="endLine">
                                                                    <int>11</int>
                                                                </property>
                                                                <property name="internalPadding">
                                                                    <null/>
                                                                </property>
                                                                <property name="length">
                                                                    <int>17</int>
                                                                </property>
                                                                <property name="offset">
                                                                    <int>6</int>
                                                                </property>
                                                                <property name="startLine">
                                                                    <int>11</int>
                                                                </property>
                                                                <property name="startLinePadding">
                                                                    <int>0</int>
                                                                </property>
                                                            </object>
                                                        </item>
                                                        <item key="LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset">
                                                            <int>1</int>
                                                        </item>
                                                    </array>
                                                </item>
                                            </data>
                                            <string><![CDATA[Class::method()]]></string>
                                        </node>
                                    </node>
                                </node>
                            </markdown>
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
