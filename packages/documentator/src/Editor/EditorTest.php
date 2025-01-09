<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Append;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_values;
use function iterator_to_array;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(Editor::class)]
final class EditorTest extends TestCase {
    public function testMutate(): void {
        $lines   = [
            0  => 'a b c d',
            1  => 'e f g h',
            2  => 'i j k l',
            3  => 'm n o p',
            4  => '',
            5  => 'q r s t',
            6  => 'u v w x',
            7  => '',
            8  => 'y z',
            9  => '',
            10 => '> a b c d',
            11 => '> e f g h',
            12 => '>',
            13 => '> i j k l',
            14 => '>',
            15 => '>',
        ];
        $changes = [
            [new Location(1, 1, 2, 3), "123\n345\n567"],
            [new Location(2, 4, 4, 4), '123'],
            [new Location(6, 8, 4, 4), "123\n345"],
            [new Location(11, 12, 4, 3, 2), "123\n345\n567"],
            [new Location(12, 12, 5, 2, 2), null],
            [new Location(14, 16, 4, 3, 2), '123'],
            [new Location(PHP_INT_MAX, PHP_INT_MAX), "added line a\n"],
            [new Location(PHP_INT_MAX, PHP_INT_MAX), "added line b\n"],
        ];
        $editor  = new readonly class($lines, 1) extends Editor {
            /**
             * @return list<string>
             */
            public function getLines(): array {
                return $this->lines;
            }
        };

        $actual   = $editor->mutate($changes);
        $expected = [
            'a 123',
            '345',
            '567 d',
            'e f 123',
            'o p',
            '',
            'q r 123',
            '345',
            'y z',
            '',
            '> a b 123',
            '> 345',
            '> 567 g',
            '>',
            '> i j 123',
            'added line a',
            '',
            'added line b',
            '',
        ];

        self::assertNotSame($editor, $actual);
        self::assertEquals($lines, $editor->getLines());
        self::assertSame($expected, $actual->getLines());
    }

    public function testPrepare(): void {
        $editor   = new readonly class(['L1', 'L2']) extends Editor {
            /**
             * @inheritDoc
             */
            #[Override]
            public function prepare(iterable $changes): array {
                return parent::prepare($changes);
            }
        };
        $changes  = [
            [new Location(10, 10, 15, 10), 'a'],
            [new Location(10, 10, 10, null), 'b'],
            [new Location(12, 15, 5, 10), 'c'],
        ];
        $expected = [
            [iterator_to_array(new Location(12, 15, 5, 10)), 'c'],
            [iterator_to_array(new Location(10, 10, 10, null)), 'b'],
            [iterator_to_array(new Location(10, 10, 15, 10)), 'a'],
        ];

        self::assertEquals($expected, $editor->prepare($changes));
    }

    public function testRemoveOverlaps(): void {
        $editor   = new readonly class([]) extends Editor {
            /**
             * @inheritDoc
             */
            #[Override]
            public function removeOverlaps(array $changes): array {
                return parent::removeOverlaps($changes);
            }
        };
        $changes  = [
            0  => [array_values(iterator_to_array(new Location(18, 18, 5, 10))), 'a'],
            1  => [array_values(iterator_to_array(new Location(17, 17, 11, 10))), 'b'],
            2  => [array_values(iterator_to_array(new Location(17, 17, 5, 10))), 'c'],
            3  => [array_values(iterator_to_array(new Location(14, 15, 5, 10))), 'd'],
            4  => [array_values(iterator_to_array(new Location(12, 15, 5, 10))), 'e'],
            5  => [array_values(iterator_to_array(new Location(10, 10, 10, null))), 'f'],
            6  => [array_values(iterator_to_array(new Location(10, 10, 15, 10))), 'g'],
            7  => [array_values(iterator_to_array(new Location(9, 9, 39, 11))), 'h'],
            8  => [array_values(iterator_to_array(new Location(9, 9, 50, null))), 'i'],
            9  => [array_values(iterator_to_array(new Location(9, 9, 40, 10))), 'j'],
            10 => [array_values(iterator_to_array(new Location(PHP_INT_MAX, PHP_INT_MAX))), 'k'],
            11 => [array_values(iterator_to_array(new Append())), 'l'],
        ];
        $expected = [
            0  => [iterator_to_array(new Location(18, 18, 5, 10)), 'a'],
            1  => [iterator_to_array(new Location(17, 17, 11, 10)), 'b'],
            3  => [iterator_to_array(new Location(14, 15, 5, 10)), 'd'],
            5  => [iterator_to_array(new Location(10, 10, 10, null)), 'f'],
            7  => [iterator_to_array(new Location(9, 9, 39, 11)), 'h'],
            8  => [iterator_to_array(new Location(9, 9, 50, null)), 'i'],
            10 => [iterator_to_array(new Location(PHP_INT_MAX, PHP_INT_MAX)), 'k'],
            11 => [iterator_to_array(new Append()), 'l'],
        ];

        self::assertEquals($expected, $editor->removeOverlaps($changes));
    }

    public function testExpand(): void {
        $editor   = new readonly class([]) extends Editor {
            /**
             * @inheritDoc
             */
            #[Override]
            public function expand(array $changes): array {
                return parent::expand($changes);
            }
        };
        $changes  = [
            [array_values(iterator_to_array(new Location(PHP_INT_MAX, PHP_INT_MAX))), "new line aa\nnew line ab"],
            [array_values(iterator_to_array(new Location(6, 6, 5, 10, 2))), "text aa\ntext ab"],
            [array_values(iterator_to_array(new Location(4, 5, 5, 5, 1))), "text ba\ntext bb"],
            [array_values(iterator_to_array(new Location(2, 3, 5, null))), 'text c'],
            [array_values(iterator_to_array(new Location(1, 1, 5, 10))), "text da\ntext db\ntext dc"],
            [array_values(iterator_to_array(new Append())), "new line ba\nnew line bb"],
        ];
        $expected = [
            [new Coordinate(6, 7, 10, 2), ['text aa', 'text ab']],
            [new Coordinate(5, 1, 5, 1), ['text bb']],
            [new Coordinate(4, 6, null, 1), ['text ba']],
            [new Coordinate(3, 0, null, 0), []],
            [new Coordinate(2, 5, null, 0), ['text c']],
            [new Coordinate(1, 5, 10, 0), ['text da', 'text db', 'text dc']],
            [new Coordinate(PHP_INT_MAX, 0, null, 0), ['new line ba', 'new line bb']],
            [new Coordinate(PHP_INT_MAX, 0, null, 0), ['new line aa', 'new line ab']],
        ];

        self::assertEquals($expected, $editor->expand($changes));
    }

    public function testGetText(): void {
        $editor = new Editor(
            [
                0 => 'a b c d',
                1 => 'e f g h',
                2 => 'i j k l',
                3 => 'm n o p',
                4 => '',
                5 => 'q r s t',
                6 => 'u v w x',
            ],
            1,
        );

        self::assertNull($editor->getText(new Location(25, 25, 0)));
        self::assertEquals('f g', $editor->getText(new Location(2, 2, 2, 3)));
        self::assertEquals(
            <<<'TEXT'
            k l
            m n o p

            q r s
            TEXT,
            $editor->getText(new Location(3, 6, 4, 5)),
        );
        self::assertEquals(
            <<<'TEXT'
            f g
            TEXT,
            $editor->getText([new Coordinate(2, 2, 3)]),
        );
    }
}
