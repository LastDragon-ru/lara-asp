<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks;

use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\TestCase;
use LastDragon_ru\GraphQLPrinter\Testing\TestSettings;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PropertyBlock::class)]
final class PropertyBlockTest extends TestCase {
    public function testSerialize(): void {
        $name      = 'name';
        $used      = 123;
        $level     = 2;
        $space     = '  ';
        $separator = ':';
        $collector = new Collector();
        $content   = 'abc abcabc abcabc abcabc abc';
        $settings  = (new TestSettings())->setSpace($space);
        $context   = new Context($settings, null, null);
        $block     = new class($context, $content) extends Block {
            public function __construct(
                Context $context,
                protected string $serialized,
            ) {
                parent::__construct($context);
            }

            #[Override]
            protected function content(Collector $collector, int $level, int $used): string {
                return $this->serialized;
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

            #[Override]
            protected function getSeparator(): string {
                return $this->separator;
            }
        };
        $expected  = "{$name}{$separator}{$space}{$content}";
        $actual    = $property->serialize($collector, $level, $used);

        self::assertSame($expected, $actual);
    }
}
