<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

use function mb_strlen;

/**
 * @internal
 */
#[CoversClass(PropertyBlock::class)]
class PropertyBlockTest extends TestCase {
    public function testToString(): void {
        $name      = 'name';
        $used      = 123;
        $level     = 2;
        $space     = '  ';
        $separator = ':';
        $content   = 'abc abcabc abcabc abcabc abc';
        $settings  = (new TestSettings())->setSpace($space);
        $context   = new Context($settings, null, null);
        $block     = new class($context, $level, $used, $content) extends Block {
            public function __construct(
                Context $context,
                int $level,
                int $used,
                protected string $content,
            ) {
                parent::__construct($context, $level, $used);
            }

            protected function content(): string {
                return $this->content;
            }
        };
        $property  = new class($context, $name, $block, $separator) extends PropertyBlock {
            public function __construct(
                Context $context,
                string $name,
                Block $block,
                private string $separator,
            ) {
                parent::__construct($context, $name, $block);
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
