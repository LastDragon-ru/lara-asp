<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Locator;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Editor::class)]
final class EditorTest extends TestCase {
    public function testMutate(): void {
        $lines    = [
            1  => 'a b c d',
            2  => 'e f g h',
            3  => 'i j k l',
            4  => 'm n o p',
            5  => '',
            6  => 'q r s t',
            7  => 'u v w x',
            8  => '',
            9  => 'y z',
            10 => '',
            11 => '> a b c d',
            12 => '> e f g h',
            13 => '>',
            14 => '> i j k l',
            15 => '>',
        ];
        $editor   = new Editor($lines);
        $changes  = [
            [new Locator(1, 1, 2, 3), '123'],
            [new Locator(2, 4, 4, 4), '123'],
            [new Locator(6, 8, 4, 4), "123\n345"],
            [new Locator(11, 12, 4, 3, 2), "123\n345"],
            [new Locator(14, 15, 4, 3, 2), '123'],
        ];
        $actual   = $editor->mutate($changes);
        $expected = [
            1  => 'a 123 d',
            2  => 'e f 123',
            3  => '',
            4  => 'o p',
            5  => '',
            6  => 'q r 123',
            7  => '345',
            8  => '',
            9  => 'y z',
            10 => '',
            11 => '> a b 123',
            12 => '> 345 g h',
            13 => '>',
            14 => '> i j 123',
        ];

        self::assertNotSame($editor, $actual);
        self::assertEquals($lines, $editor->getLines());
        self::assertSame($expected, $actual->getLines());
    }

    public function testRemoveOverlaps(): void {
        $editor   = new class([]) extends Editor {
            /**
             * @inheritDoc
             */
            #[Override]
            public function removeOverlaps(array $changes): array {
                return parent::removeOverlaps($changes);
            }
        };
        $changes  = [
            0 => [new Locator(10, 10, 15, 10), 'a'],
            1 => [new Locator(10, 10, 10, null), 'b'],
            2 => [new Locator(12, 15, 5, 10), 'c'],
            3 => [new Locator(14, 15, 5, 10), 'd'],
            4 => [new Locator(17, 17, 5, 10), 'e'],
            5 => [new Locator(17, 17, 11, 10), 'f'],
            6 => [new Locator(18, 18, 5, 10), 'g'],
        ];
        $expected = [
            1 => [new Locator(10, 10, 10, null), 'b'],
            3 => [new Locator(14, 15, 5, 10), 'd'],
            5 => [new Locator(17, 17, 11, 10), 'f'],
            6 => [new Locator(18, 18, 5, 10), 'g'],
        ];

        self::assertEquals($expected, $editor->removeOverlaps($changes));
    }

    public function testExpand(): void {
        $editor   = new class([]) extends Editor {
            /**
             * @inheritDoc
             */
            #[Override]
            public function expand(array $changes): array {
                return parent::expand($changes);
            }
        };
        $changes  = [
            [new Locator(1, 1, 5, 10), 'text'],
            [new Locator(2, 3, 5, null), 'text'],
            [new Locator(4, 5, 5, 5, 1), "text a\ntext b"],
            [new Locator(6, 6, 5, 10, 2), "text a\ntext b"],
        ];
        $expected = [
            [new Coordinate(6, 7, 10), 2, 'text a'],
            [new Coordinate(5, 1, 5), 1, 'text b'],
            [new Coordinate(4, 6, null), 1, 'text a'],
            [new Coordinate(3, 0, null), 0, null],
            [new Coordinate(2, 5, null), 0, 'text'],
            [new Coordinate(1, 5, 10), 0, 'text'],
        ];

        self::assertEquals($expected, $editor->expand($changes));
    }

    public function testGetText(): void {
        $editor = new Editor([
            0 => 'a b c d',
            1 => 'e f g h',
            2 => 'i j k l',
            3 => 'm n o p',
            4 => '',
            5 => 'q r s t',
            6 => 'u v w x',
        ]);

        self::assertNull($editor->getText(new Locator(25, 25, 0)));
        self::assertEquals('f g', $editor->getText(new Locator(1, 1, 2, 3)));
        self::assertEquals(
            <<<'TEXT'
            k l
            m n o p

            q r s
            TEXT,
            $editor->getText(new Locator(2, 5, 4, 5)),
        );
        self::assertEquals(
            <<<'TEXT'
            f g
            TEXT,
            $editor->getText(new Coordinate(1, 2, 3)),
        );
    }
}
