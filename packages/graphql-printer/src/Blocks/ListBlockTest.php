<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

use function assert;

/**
 * @internal
 */
#[CoversClass(ListBlock::class)]
class ListBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSerialize
     *
     * @param array<string, Block> $blocks
     */
    public function testSerialize(
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
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $list      = new ListBlockTest__ListBlock(
            $context,
            $blocks,
            $normalized,
            $wrapped,
            $prefix,
            $suffix,
            $separator,
            $multilineSeparator,
        );

        self::assertEquals($expected, $list->serialize($collector, $level, $used));
    }

    public function testStatistics(): void {
        $collector = new Collector();
        $context   = new Context(new TestSettings(), null, null);
        $items     = [
            new ListBlockTest__StatisticsBlock(['ta'], ['da']),
            new ListBlockTest__StatisticsBlock(['tb'], ['db']),
        ];
        $list      = new class($context, $items) extends ListBlock {
            protected function block(string|int $key, mixed $item): Block {
                assert($item instanceof Block);

                return $item;
            }
        };

        self::assertNotEmpty($list->serialize($collector, 0, 0));
        self::assertEquals(['ta' => 'ta', 'tb' => 'tb'], $collector->getUsedTypes());
        self::assertEquals(['da' => 'da', 'db' => 'db'], $collector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array<mixed>>
     */
    public static function dataProviderSerialize(): array {
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
                        new ListBlockTest__Block('block a'),
                    ],
                ],
                'one multi-line block'                          => [
                    <<<'STRING'
                    block a
                    --multiline
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
                        new ListBlockTest__Block("block a\n--multiline"),
                    ],
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
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__Block('block b'),
                    ],
                ],
                'long block list'                               => [
                    <<<'STRING'
                    block b
                    block a
                    STRING,
                    $settings->setLineLength(19),
                    0,
                    5,
                    false,
                    true,
                    '',
                    '',
                    ', ',
                    '',
                    [
                        new ListBlockTest__Block('block b'),
                        new ListBlockTest__Block('block a'),
                    ],
                ],
                'short block list with multiline block'         => [
                    <<<'STRING'
                    block a

                    block b
                    --multiline
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
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__Block("block b\n--multiline"),
                    ],
                ],
                'block list with multiline blocks'              => [
                    <<<'STRING'
                    block a
                    --multiline

                    block b
                    block c

                    block d
                    --multiline

                    block e
                    block f

                    block g
                    --multiline
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
                        new ListBlockTest__Block("block a\n--multiline"),
                        new ListBlockTest__Block('block b'),
                        new ListBlockTest__Block('block c'),
                        new ListBlockTest__Block("block d\n--multiline"),
                        new ListBlockTest__Block('block e'),
                        new ListBlockTest__Block('block f'),
                        new ListBlockTest__Block("block g\n--multiline"),
                    ],
                ],
                'block list with multiline blocks without wrap' => [
                    <<<'STRING'
                    block c
                    --multiline
                    block b
                    block a
                    --multiline
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
                        new ListBlockTest__Block("block c\n--multiline"),
                        new ListBlockTest__Block('block b'),
                        new ListBlockTest__Block("block a\n--multiline"),
                    ],
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
                        new ListBlockTest__Block('block b'),
                        new ListBlockTest__Block('block a'),
                    ],
                ],
                'multi-line with level'                         => [
                    <<<'STRING'
                    block a
                    --multiline
                        block b
                        --multiline
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
                        new ListBlockTest__Block("block a\n--multiline"),
                        new ListBlockTest__Block("block b\n    --multiline"),
                    ],
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
                        new ListBlockTest__NamedBlock('a', 'block a'),
                    ],
                ],
                'one multi-line block'                          => [
                    <<<'STRING'
                    a: block a
                    --multiline
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
                        new ListBlockTest__NamedBlock('a', "block a\n--multiline"),
                    ],
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
                        new ListBlockTest__NamedBlock('a', 'block a'),
                        new ListBlockTest__NamedBlock('b', 'block b'),
                    ],
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
                        new ListBlockTest__NamedBlock('b', 'block b'),
                        new ListBlockTest__NamedBlock('a', 'block a'),
                    ],
                ],
                'short block list with multiline block'         => [
                    <<<'STRING'
                    a: block a

                    b: block b
                    --multiline
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
                        new ListBlockTest__NamedBlock('a', 'block a'),
                        new ListBlockTest__NamedBlock('b', "block b\n--multiline"),
                    ],
                ],
                'block list with multiline blocks'              => [
                    <<<'STRING'
                    a: block a
                    --multiline

                    b: block b
                    c: block c

                    d: block d
                    --multiline

                    e: block e
                    f: block f

                    g: block g
                    --multiline
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
                        new ListBlockTest__NamedBlock('a', "block a\n--multiline"),
                        new ListBlockTest__NamedBlock('b', 'block b'),
                        new ListBlockTest__NamedBlock('c', 'block c'),
                        new ListBlockTest__NamedBlock('d', "block d\n--multiline"),
                        new ListBlockTest__NamedBlock('e', 'block e'),
                        new ListBlockTest__NamedBlock('f', 'block f'),
                        new ListBlockTest__NamedBlock('g', "block g\n--multiline"),
                    ],
                ],
                'block list with multiline blocks without wrap' => [
                    <<<'STRING'
                    c: block c
                    --multiline
                    b: block b
                    a: block a
                    --multiline
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
                        new ListBlockTest__NamedBlock('c', "block c\n--multiline"),
                        new ListBlockTest__NamedBlock('b', 'block b'),
                        new ListBlockTest__NamedBlock('a', "block a\n--multiline"),
                    ],
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
                        new ListBlockTest__NamedBlock('b', 'block b'),
                        new ListBlockTest__NamedBlock('a', 'block a'),
                    ],
                ],
                'multi-line with level'                         => [
                    <<<'STRING'
                    a: block a
                    --multiline
                        b: block b
                        --multiline
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
                        new ListBlockTest__NamedBlock('a', "block a\n--multiline"),
                        new ListBlockTest__NamedBlock('b', "block b\n    --multiline"),
                    ],
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
                        new ListBlockTest__NamedBlock('a', 'block a'),
                    ],
                ],
                'one multi-line block'                  => [
                    <<<'STRING'
                    [
                        a: block a
                        --multiline
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
                        new ListBlockTest__NamedBlock('a', "block a\n    --multiline"),
                    ],
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
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__NamedBlock('b', 'block b'),
                    ],
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
                        new ListBlockTest__NamedBlock('b', 'block b'),
                        new ListBlockTest__NamedBlock('a', 'block a'),
                    ],
                ],
                'short block list with multiline block' => [
                    <<<'STRING'
                    [
                        block a

                        block b
                        --multiline
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
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__Block("block b\n    --multiline"),
                    ],
                ],
                'multi-line with level'                 => [
                    <<<'STRING'
                    [
                                block a
                                --multiline
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
                        new ListBlockTest__Block("block a\n            --multiline"),
                    ],
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
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__Block('block b'),
                    ],
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
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__Block('block b'),
                    ],
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
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__Block('block b'),
                    ],
                ],
            ]),
            'empty blocks'    => new ArrayDataProvider([
                'should be ignored with no used' => [
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
                        new ListBlockTest__Block(''),
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__Block(''),
                        new ListBlockTest__Block('block b'),
                        new ListBlockTest__Block(''),
                    ],
                ],
                'should be ignored with used'    => [
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
                        new ListBlockTest__Block(''),
                        new ListBlockTest__Block('block a'),
                        new ListBlockTest__Block(''),
                        new ListBlockTest__Block('block b'),
                        new ListBlockTest__Block(''),
                    ],
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
 *
 * @extends ListBlock<Block, array-key, Block>
 */
class ListBlockTest__ListBlock extends ListBlock {
    /**
     * @param iterable<string, Block> $items
     */
    public function __construct(
        Context $context,
        iterable $items,
        private bool $normalized,
        private bool $wrapped,
        private string $prefix,
        private string $suffix,
        private string $separator,
        private string $multilineSeparator,
    ) {
        parent::__construct($context, $items);
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

    protected function block(string|int $key, mixed $item): Block {
        return $item;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ListBlockTest__Block extends Block {
    public function __construct(
        protected string $serialized,
    ) {
        parent:: __construct(new Context(new TestSettings(), null, null));
    }

    protected function content(Collector $collector, int $level, int $used): string {
        return $this->serialized;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @extends PropertyBlock<Block>
 */
class ListBlockTest__NamedBlock extends PropertyBlock {
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(
        protected string $name,
        protected string $serialized,
    ) {
        parent::__construct(
            new Context(new TestSettings(), null, null),
            $name,
            new ListBlockTest__Block(''),
        );
    }

    public function getName(): string {
        return $this->name;
    }

    protected function getBlock(): Block {
        return new ListBlockTest__Block($this->serialized);
    }

    protected function space(): string {
        return ' ';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ListBlockTest__StatisticsBlock extends Block {
    /**
     * @param array<string> $types
     * @param array<string> $directives
     */
    public function __construct(
        protected array $types,
        protected array $directives,
    ) {
        parent:: __construct(new Context(new TestSettings(), null, null));
    }

    protected function content(Collector $collector, int $level, int $used): string {
        foreach ($this->types as $type) {
            $collector->addUsedType($type);
        }

        foreach ($this->directives as $directive) {
            $collector->addUsedDirective($directive);
        }

        return __METHOD__;
    }
}
