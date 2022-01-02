<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;
use PHPUnit\Framework\TestCase;

use function mb_strlen;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\BlockList
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
        array $blocks,
    ): void {
        $list = new BlockList($settings, $level, $used, $normalized, $wrapped, $prefix, $suffix);

        foreach ($blocks as $name => $block) {
            $list[$name] = $block;
        }

        self::assertEquals($expected, (string) $list);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, string}>
     */
    public function dataProviderToString(): array {
        return (new MergeDataProvider([
            'index'           => new ArrayDataProvider([
                'one single-line block'                         => [
                    <<<'STRING'
                    block a
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        new BlockListTest__Block(false, 'block a'),
                    ],
                ],
                'one multi-line block'                          => [
                    <<<'STRING'
                    block a
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        new BlockListTest__Block(true, 'block a'),
                    ],
                ],
                'short block list'                              => [
                    <<<'STRING'
                    block a, block b
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(false, 'block b'),
                    ],
                ],
                'long block list'                               => [
                    <<<'STRING'
                    block b
                    block a
                    STRING,
                    new class() extends DefaultSettings {
                        public function getLineLength(): int {
                            return 20;
                        }
                    },
                    0,
                    5,
                    false,
                    true,
                    '',
                    '',
                    [
                        new BlockListTest__Block(false, 'block b'),
                        new BlockListTest__Block(false, 'block a'),
                    ],
                ],
                'short block list with multiline block'         => [
                    <<<'STRING'
                    block a

                    block b
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(true, 'block b'),
                    ],
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
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
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
                ],
                'block list with multiline blocks without wrap' => [
                    <<<'STRING'
                    block c
                    block b
                    block a
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    false,
                    '',
                    '',
                    [
                        new BlockListTest__Block(true, 'block c'),
                        new BlockListTest__Block(false, 'block b'),
                        new BlockListTest__Block(true, 'block a'),
                    ],
                ],
                'normalized block list'                         => [
                    <<<'STRING'
                    block b, block a
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    true,
                    true,
                    '',
                    '',
                    [
                        new BlockListTest__Block(false, 'block b'),
                        new BlockListTest__Block(false, 'block a'),
                    ],
                ],
                'multi-line with level'                         => [
                    <<<'STRING'
                        block a
                    STRING,
                    new class() extends DefaultSettings {
                        public function getIndent(): string {
                            return '  ';
                        }
                    },
                    2,
                    0,
                    false,
                    false,
                    '',
                    '',
                    [
                        new BlockListTest__Block(true, 'block a'),
                    ],
                ],
            ]),
            'named'           => new ArrayDataProvider([
                'one single-line block'                         => [
                    <<<'STRING'
                    a: block a
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        'a' => new BlockListTest__Block(false, 'block a'),
                    ],
                ],
                'one multi-line block'                          => [
                    <<<'STRING'
                    a: block a
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        'a' => new BlockListTest__Block(true, 'block a'),
                    ],
                ],
                'short block list'                              => [
                    <<<'STRING'
                    a: block a, b: block b
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        'a' => new BlockListTest__Block(false, 'block a'),
                        'b' => new BlockListTest__Block(false, 'block b'),
                    ],
                ],
                'long block list'                               => [
                    <<<'STRING'
                    b: block b
                    a: block a
                    STRING,
                    new class() extends DefaultSettings {
                        public function getLineLength(): int {
                            return 20;
                        }
                    },
                    0,
                    5,
                    false,
                    true,
                    '',
                    '',
                    [
                        'b' => new BlockListTest__Block(false, 'block b'),
                        'a' => new BlockListTest__Block(false, 'block a'),
                    ],
                ],
                'short block list with multiline block'         => [
                    <<<'STRING'
                    a: block a

                    b: block b
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        'a' => new BlockListTest__Block(false, 'block a'),
                        'b' => new BlockListTest__Block(true, 'block b'),
                    ],
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
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '',
                    '',
                    [
                        'a' => new BlockListTest__Block(true, 'block a'),
                        'b' => new BlockListTest__Block(false, 'block b'),
                        'c' => new BlockListTest__Block(false, 'block c'),
                        'd' => new BlockListTest__Block(true, 'block d'),
                        'e' => new BlockListTest__Block(false, 'block e'),
                        'f' => new BlockListTest__Block(false, 'block f'),
                        'g' => new BlockListTest__Block(true, 'block g'),
                    ],
                ],
                'block list with multiline blocks without wrap' => [
                    <<<'STRING'
                    c: block c
                    b: block b
                    a: block a
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    false,
                    '',
                    '',
                    [
                        'c' => new BlockListTest__Block(true, 'block c'),
                        'b' => new BlockListTest__Block(false, 'block b'),
                        'a' => new BlockListTest__Block(true, 'block a'),
                    ],
                ],
                'normalized block list'                         => [
                    <<<'STRING'
                    a: block a, b: block b
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    true,
                    true,
                    '',
                    '',
                    [
                        'b' => new BlockListTest__Block(false, 'block b'),
                        'a' => new BlockListTest__Block(false, 'block a'),
                    ],
                ],
                'multi-line with level'                         => [
                    <<<'STRING'
                        a: block a
                    STRING,
                    new class() extends DefaultSettings {
                        public function getIndent(): string {
                            return '  ';
                        }
                    },
                    2,
                    0,
                    false,
                    false,
                    '',
                    '',
                    [
                        'a' => new BlockListTest__Block(true, 'block a'),
                    ],
                ],
            ]),
            'prefix & suffix' => new ArrayDataProvider([
                'one single-line block'                 => [
                    <<<'STRING'
                    [a: block a]
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '[',
                    ']',
                    [
                        'a' => new BlockListTest__Block(false, 'block a'),
                    ],
                ],
                'one multi-line block'                  => [
                    <<<'STRING'
                    [
                        a: block a
                    ]
                    STRING,
                    new class() extends DefaultSettings {
                        public function getIndent(): string {
                            return '    ';
                        }
                    },
                    0,
                    0,
                    false,
                    true,
                    '[',
                    ']',
                    [
                        'a' => new BlockListTest__Block(true, 'block a'),
                    ],
                ],
                'short block list'                      => [
                    <<<'STRING'
                    [block a, b: block b]
                    STRING,
                    new DefaultSettings(),
                    0,
                    0,
                    false,
                    true,
                    '[',
                    ']',
                    [
                        0   => new BlockListTest__Block(false, 'block a'),
                        'b' => new BlockListTest__Block(false, 'block b'),
                    ],
                ],
                'long block list'                       => [
                    <<<'STRING'
                    [
                        b: block b
                        a: block a
                    ]
                    STRING,
                    new class() extends DefaultSettings {
                        public function getLineLength(): int {
                            return 20;
                        }

                        public function getIndent(): string {
                            return '    ';
                        }
                    },
                    0,
                    5,
                    false,
                    true,
                    '[',
                    ']',
                    [
                        'b' => new BlockListTest__Block(false, 'block b'),
                        'a' => new BlockListTest__Block(false, 'block a'),
                    ],
                ],
                'short block list with multiline block' => [
                    <<<'STRING'
                    [
                        block a

                        block b
                    ]
                    STRING,
                    new class() extends DefaultSettings {
                        public function getIndent(): string {
                            return '    ';
                        }
                    },
                    0,
                    0,
                    false,
                    true,
                    '[',
                    ']',
                    [
                        new BlockListTest__Block(false, 'block a'),
                        new BlockListTest__Block(true, 'block b'),
                    ],
                ],
                'multi-line with level'                 => [
                    <<<'STRING'
                    [
                                block a
                            ]
                    STRING,
                    new class() extends DefaultSettings {
                        public function getIndent(): string {
                            return '    ';
                        }
                    },
                    2,
                    0,
                    false,
                    false,
                    '[',
                    ']',
                    [
                        new BlockListTest__Block(true, 'block a'),
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
 */
class BlockListTest__Block extends Block {
    public function __construct(
        protected bool $multiline,
        protected string $content,
    ) {
        parent::__construct(new DefaultSettings());
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
