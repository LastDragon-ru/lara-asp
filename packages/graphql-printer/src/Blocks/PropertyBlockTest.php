<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

use function mb_strlen;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\PropertyBlock
 */
class PropertyBlockTest extends TestCase {
    public function testToString(): void {
        $name      = 'name';
        $used      = 123;
        $level     = 2;
        $space     = '  ';
        $separator = ':';
        $content   = 'abc abcabc abcabc abcabc abc';
        $settings  = (new TestSettings())->setSpace($space);
        $block     = new class($settings, $level, $used, $content) extends Block {
            public function __construct(
                Settings $settings,
                int $level,
                int $used,
                protected string $content,
            ) {
                parent::__construct($settings, $level, $used);
            }

            protected function content(): string {
                return $this->content;
            }
        };
        $property  = new class($settings, $name, $block, $separator) extends PropertyBlock {
            public function __construct(
                Settings $settings,
                string $name,
                Block $block,
                private string $separator,
            ) {
                parent::__construct($settings, $name, $block);
            }

            public function getUsed(): int {
                return parent::getUsed();
            }

            public function getLevel(): int {
                return parent::getLevel();
            }

            protected function getSeparator(): string {
                return $this->separator;
            }
        };
        $expected  = "{$name}{$separator}{$space}{$content}";

        self::assertEquals($used, $property->getUsed());
        self::assertEquals($level, $property->getLevel());
        self::assertEquals($expected, (string) $property);
        self::assertEquals(mb_strlen($expected), mb_strlen((string) $property));
        self::assertEquals(mb_strlen($expected), $property->getLength());
    }
}
