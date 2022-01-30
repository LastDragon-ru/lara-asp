<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;

use function mb_strlen;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList
 */
class BlockListTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
     * @dataProvider dataProviderToString
     *
     * @param array<string, Block> $blocks
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        bool $normalized,
        bool $wrapped,
        string $prefix,
        string $suffix,
        string $separator,
        string $multilineSeparator,
        array $blocks,
        int $count,
    ): void {
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $list     = new BlockListTest__BlockList(
            $settings,
            $level,
            $used,
            $normalized,
            $wrapped,
            $prefix,
            $suffix,
            $separator,
            $multilineSeparator,
        );

        foreach ($blocks as $name => $block) {
            $list[$name] = $block;
        }

        self::assertEquals($expected, (string) $list);
        self::assertCount($count, $list);
    }

    /**
     * @covers ::content
     * @covers ::analyze
     */
    public function testStatistics(): void {
        $settings = new TestSettings();
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $list     = new class($settings) extends BlockList {
            // empty
        };
        $list[]   = new BlockListTest__StatisticsBlock(['ta'], ['da']);
        $list[]   = new BlockListTest__StatisticsBlock(['tb'], ['db']);

        self::assertEquals(['ta' => 'ta', 'tb' => 'tb'], $list->getUsedTypes());
        self::assertEquals(['da' => 'da', 'db' => 'db'], $list->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array<mixed>>
     */
    public function dataProviderToString(): array {
        $settings = new TestSettings();

        return (new MergeDataProvider([
            'index'           => new ArrayDataProvider([
                'one single-line block'                         => [
                    <<<'STRING'
                    block a
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(false, 'block a'),
                    ],
                    1,
                ],
                'one multi-line block'                          => [
                    <<<'STRING'
                    block a
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(true, 'block a'),
                    ],
                    1,
                ],
                'short block list'                              => [
                    <<<'STRING'
                    block a, block b
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(false, 'block b'),
                    ],
                    2,
                ],
                'long block list'                               => [
                    <<<'STRING'
                    block b
                    block a
                    STRING,
                    $settings->setLineLength(20),
                    0,
                    5,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(false, 'block b'),
                        new BlockListTest__Block(false, 'block a'),
                    ],
                    2,
                ],
                'short block list with multiline block'         => [
                    <<<'STRING'
                    block a

                    block b
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(true, 'block b'),
                    ],
                    2,
                ],
                'block list with multiline blocks'              => [
                    <<<'STRING'
                    block a

                    block b
                    block c

                    block d

                    block e
                    block f

                    block g
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(true, 'block a'),
                        new BlockListTest__Block(false, 'block b'),
                        new BlockListTest__Block(false, 'block c'),
                        new BlockListTest__Block(true, 'block d'),
                        new BlockListTest__Block(false, 'block e'),
                        new BlockListTest__Block(false, 'block f'),
                        new BlockListTest__Block(true, 'block g'),
                    ],
                    7,
                ],
                'block list with multiline blocks without wrap' => [
                    <<<'STRING'
                    block c
                    block b
                    block a
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    false,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(true, 'block c'),
                        new BlockListTest__Block(false, 'block b'),
                        new BlockListTest__Block(true, 'block a'),
                    ],
                    3,
                ],
                'normalized block list'                         => [
                    <<<'STRING'
                    block b, block a
                    STRING,
                    $settings,
                    0,
                    0,
                    true,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(false, 'block b'),
                        new BlockListTest__Block(false, 'block a'),
                    ],
                    2,
                ],
                'multi-line with level'                         => [
                    <<<'STRING'
                    block a
                        block b
                    STRING,
                    $settings->setIndent('  '),
                    2,
                    0,
                    false,
                    false,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(true, 'block a'),
                        new BlockListTest__Block(true, 'block b'),
                    ],
                    2,
                ],
            ]),
            'named'           => new ArrayDataProvider([
                'one single-line block'                         => [
                    <<<'STRING'
                    a: block a
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('a', false, 'block a'),
                    ],
                    1,
                ],
                'one multi-line block'                          => [
                    <<<'STRING'
                    a: block a
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('a', true, 'block a'),
                    ],
                    1,
                ],
                'short block list'                              => [
                    <<<'STRING'
                    a: block a, b: block b
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('a', false, 'block a'),
                        new BlockListTest__NamedBlock('b', false, 'block b'),
                    ],
                    2,
                ],
                'long block list'                               => [
                    <<<'STRING'
                    b: block b
                    a: block a
                    STRING,
                    $settings->setLineLength(20),
                    0,
                    5,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('b', false, 'block b'),
                        new BlockListTest__NamedBlock('a', false, 'block a'),
                    ],
                    2,
                ],
                'short block list with multiline block'         => [
                    <<<'STRING'
                    a: block a

                    b: block b
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('a', false, 'block a'),
                        new BlockListTest__NamedBlock('b', true, 'block b'),
                    ],
                    2,
                ],
                'block list with multiline blocks'              => [
                    <<<'STRING'
                    a: block a

                    b: block b
                    c: block c

                    d: block d

                    e: block e
                    f: block f

                    g: block g
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('a', true, 'block a'),
                        new BlockListTest__NamedBlock('b', false, 'block b'),
                        new BlockListTest__NamedBlock('c', false, 'block c'),
                        new BlockListTest__NamedBlock('d', true, 'block d'),
                        new BlockListTest__NamedBlock('e', false, 'block e'),
                        new BlockListTest__NamedBlock('f', false, 'block f'),
                        new BlockListTest__NamedBlock('g', true, 'block g'),
                    ],
                    7,
                ],
                'block list with multiline blocks without wrap' => [
                    <<<'STRING'
                    c: block c
                    b: block b
                    a: block a
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    false,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('c', true, 'block c'),
                        new BlockListTest__NamedBlock('b', false, 'block b'),
                        new BlockListTest__NamedBlock('a', true, 'block a'),
                    ],
                    3,
                ],
                'normalized block list'                         => [
                    <<<'STRING'
                    a: block a, b: block b
                    STRING,
                    $settings,
                    0,
                    0,
                    true,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('b', false, 'block b'),
                        new BlockListTest__NamedBlock('a', false, 'block a'),
                    ],
                    2,
                ],
                'multi-line with level'                         => [
                    <<<'STRING'
                    a: block a
                        b: block b
                    STRING,
                    $settings->setIndent('  '),
                    2,
                    0,
                    false,
                    false,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('a', true, 'block a'),
                        new BlockListTest__NamedBlock('b', true, 'block b'),
                    ],
                    2,
                ],
            ]),
            'prefix & suffix' => new ArrayDataProvider([
                'one single-line block'                 => [
                    <<<'STRING'
                    [a: block a]
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '[',
                    ']',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('a', false, 'block a'),
                    ],
                    1,
                ],
                'one multi-line block'                  => [
                    <<<'STRING'
                    [
                        a: block a
                    ]
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '[',
                    ']',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('a', true, 'block a'),
                    ],
                    1,
                ],
                'short block list'                      => [
                    <<<'STRING'
                    [block a, b: block b]
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '[',
                    ']',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__NamedBlock('b', false, 'block b'),
                    ],
                    2,
                ],
                'long block list'                       => [
                    <<<'STRING'
                    [
                        b: block b
                        a: block a
                    ]
                    STRING,
                    $settings->setLineLength(20),
                    0,
                    5,
                    false,
                    true,
                    '[',
                    ']',
                    ', ',
                    '',
                    [
                        new BlockListTest__NamedBlock('b', false, 'block b'),
                        new BlockListTest__NamedBlock('a', false, 'block a'),
                    ],
                    2,
                ],
                'short block list with multiline block' => [
                    <<<'STRING'
                    [
                        block a

                        block b
                    ]
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '[',
                    ']',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(true, 'block b'),
                    ],
                    2,
                ],
                'multi-line with level'                 => [
                    <<<'STRING'
                    [
                                block a
                            ]
                    STRING,
                    $settings,
                    2,
                    0,
                    false,
                    false,
                    '[',
                    ']',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(true, 'block a'),
                    ],
                    1,
                ],
                'empty'                                 => [
                    '',
                    $settings,
                    0,
                    0,
                    false,
                    false,
                    '[',
                    ']',
                    ', ',
                    '',
                    [],
                    0,
                ],
            ]),
            'separators'      => new ArrayDataProvider([
                'single-line'          => [
                    <<<'STRING'
                    block a | block b
                    STRING,
                    $settings,
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    ' | ',
                    '||',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(false, 'block b'),
                    ],
                    2,
                ],
                'multiline'            => [
                    <<<'STRING'
                    || block a
                    || block b
                    STRING,
                    $settings,
                    0,
                    120,
                    false,
                    true,
                    '',
                    '',
                    '|',
                    '|| ',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(false, 'block b'),
                    ],
                    2,
                ],
                'multiline and indent' => [
                    <<<'STRING'
                    || block a
                        || block b
                    STRING,
                    $settings,
                    1,
                    120,
                    false,
                    true,
                    '',
                    '',
                    '|',
                    '|| ',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(false, 'block b'),
                    ],
                    2,
                ],
            ]),
            'empty blocks'    => new ArrayDataProvider([
                'should be ignored' => [
                    <<<'STRING'
                    block a
                    block b
                    STRING,
                    $settings,
                    0,
                    120,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new BlockListTest__Block(false, ''),
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(true, ''),
                        new BlockListTest__Block(false, 'block b'),
                        new BlockListTest__Block(false, ''),
                    ],
                    2,
                ],
            ]),
        ]))->getData();
    }
    //</editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BlockListTest__BlockList extends BlockList {
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        private bool $normalized,
        private bool $wrapped,
        private string $prefix,
        private string $suffix,
        private string $separator,
        private string $multilineSeparator,
    ) {
        parent::__construct($settings, $level, $used);
    }

    protected function isWrapped(): bool {
        return $this->wrapped;
    }

    protected function isNormalized(): bool {
        return $this->normalized;
    }

    protected function getPrefix(): string {
        return $this->prefix;
    }

    protected function getSuffix(): string {
        return $this->suffix;
    }

    protected function getSeparator(): string {
        return $this->separator;
    }

    protected function getMultilineItemPrefix(): string {
        return $this->multilineSeparator;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BlockListTest__Block extends Block {
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(
        protected bool $multiline,
        protected string $content,
    ) {
        // empty
    }

    protected function getContent(): string {
        return $this->content;
    }

    public function getLength(): int {
        return mb_strlen($this->getContent());
    }

    public function isMultiline(): bool {
        return $this->multiline;
    }

    protected function content(): string {
        return '';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BlockListTest__NamedBlock extends Property {
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(
        protected string $name,
        protected bool $multiline,
        protected string $content,
    ) {
        // empty
    }

    public function getName(): string {
        return $this->name;
    }

    protected function getBlock(): Block {
        return new BlockListTest__Block($this->multiline, $this->content);
    }

    protected function space(): string {
        return ' ';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BlockListTest__StatisticsBlock extends Block {
    /**
     * @param array<string> $types
     * @param array<string> $directives
     *
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(array $types, array $directives) {
        foreach ($types as $type) {
            $this->addUsedType($type);
        }

        foreach ($directives as $directive) {
            $this->addUsedDirective($directive);
        }
    }

    public function isEmpty(): bool {
        return false;
    }

    protected function content(): string {
        return '';
    }
}
