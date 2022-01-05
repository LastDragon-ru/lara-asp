<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use PHPUnit\Framework\TestCase;

use function mb_strlen;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\NamedBlock
 */
class NamedBlockTest extends TestCase {
    /**
     * @covers ::__toString
     * @covers ::getLength
     * @covers ::getLevel
     * @covers ::getUsed
     */
    public function testToString(): void {
        $name      = 'name';
        $used      = 123;
        $level     = 2;
        $space     = '  ';
        $separator = ':';
        $content   = 'abc abcabc abcabc abcabc abc';
        $settings  = new class($space) extends DefaultSettings {
            public function __construct(
                protected string $space,
            ) {
                parent::__construct();
            }

            public function getSpace(): string {
                return $this->space;
            }
        };
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
        $named     = new class($settings, $name, $block, $separator) extends NamedBlock {
            public function getUsed(): int {
                return parent::getUsed();
            }

            public function getLevel(): int {
                return parent::getLevel();
            }
        };
        $expected  = "{$name}{$separator}{$space}{$content}";

        self::assertEquals($used, $named->getUsed());
        self::assertEquals($level, $named->getLevel());
        self::assertEquals($expected, (string) $named);
        self::assertEquals(mb_strlen($expected), mb_strlen((string) $named));
        self::assertEquals(mb_strlen($expected), $named->getLength());
    }
}
