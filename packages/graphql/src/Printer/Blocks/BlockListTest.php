<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings\DefaultSettings;
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
        int $reserved,
        bool $normalized,
        bool $wrapped,
        array $blocks,
    ): void {
        $list = new BlockList($settings, $level, $reserved, $normalized, $wrapped);

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
        return [
            'one single-line block'                         => [
                <<<'STRING'
                block a
                STRING,
                new DefaultSettings(),
                0,
                0,
                false,
                true,
                [
                    'a' => new BlockListTest__Block(false, 'block a'),
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
                [
                    'a' => new BlockListTest__Block(true, 'block a'),
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
                [
                    'a' => new BlockListTest__Block(false, 'block a'),
                    'b' => new BlockListTest__Block(false, 'block b'),
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
                [
                    'b' => new BlockListTest__Block(false, 'block b'),
                    'a' => new BlockListTest__Block(false, 'block a'),
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
                [
                    'a' => new BlockListTest__Block(false, 'block a'),
                    'b' => new BlockListTest__Block(true, 'block b'),
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
                block c
                block b
                block a
                STRING,
                new DefaultSettings(),
                0,
                0,
                false,
                false,
                [
                    'c' => new BlockListTest__Block(true, 'block c'),
                    'b' => new BlockListTest__Block(false, 'block b'),
                    'a' => new BlockListTest__Block(true, 'block a'),
                ],
            ],
            'normalized block list'                         => [
                <<<'STRING'
                block a, block b
                STRING,
                new DefaultSettings(),
                0,
                0,
                true,
                true,
                [
                    'b' => new BlockListTest__Block(false, 'block b'),
                    'a' => new BlockListTest__Block(false, 'block a'),
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
                [
                    'a' => new BlockListTest__Block(true, 'block a'),
                ],
            ],
        ];
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

    protected function getLength(): int {
        return mb_strlen($this->getContent());
    }

    public function isMultiline(): bool {
        return $this->multiline;
    }

    protected function serialize(): string {
        return '';
    }

    protected function isNormalized(): bool {
        return false;
    }
}
