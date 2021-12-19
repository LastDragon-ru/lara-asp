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
        int $reserved,
        bool $normalized,
        array $blocks,
    ): void {
        $list = new class($settings, $reserved, $normalized) extends BlockList {
            public function __construct(
                Settings $settings,
                int $reserved,
                protected bool $normalized,
            ) {
                parent::__construct($settings, $reserved);
            }

            protected function isNormalized(): bool {
                return $this->normalized;
            }
        };

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
            'one single-line block'                 => [
                <<<'STRING'
                block a
                STRING,
                new DefaultSettings(),
                0,
                false,
                [
                    'a' => new BlockListTest__Block(false, 'block a'),
                ],
            ],
            'one multi-line block'                  => [
                <<<'STRING'
                block a
                STRING,
                new DefaultSettings(),
                0,
                false,
                [
                    'a' => new BlockListTest__Block(true, 'block a'),
                ],
            ],
            'short block list'                      => [
                <<<'STRING'
                block a, block b
                STRING,
                new DefaultSettings(),
                0,
                false,
                [
                    'a' => new BlockListTest__Block(false, 'block a'),
                    'b' => new BlockListTest__Block(false, 'block b'),
                ],
            ],
            'long block list'                       => [
                <<<'STRING'
                block b
                block a
                STRING,
                new class() extends DefaultSettings {
                    public function getLineLength(): int {
                        return 20;
                    }
                },
                5,
                false,
                [
                    'b' => new BlockListTest__Block(false, 'block b'),
                    'a' => new BlockListTest__Block(false, 'block a'),
                ],
            ],
            'short block list with multiline block' => [
                <<<'STRING'
                block a

                block b
                STRING,
                new DefaultSettings(),
                0,
                false,
                [
                    'a' => new BlockListTest__Block(false, 'block a'),
                    'b' => new BlockListTest__Block(true, 'block b'),
                ],
            ],
            'block list with multiline blocks'      => [
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
                false,
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
            'normalized block list'                 => [
                <<<'STRING'
                block a, block b
                STRING,
                new DefaultSettings(),
                0,
                true,
                [
                    'b' => new BlockListTest__Block(false, 'block b'),
                    'a' => new BlockListTest__Block(false, 'block a'),
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
