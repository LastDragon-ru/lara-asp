<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * @internal
 */
#[CoversClass(Editor::class)]
final class EditorTest extends TestCase {
    public function testMutate(): void {
        $lines   = [
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
            16 => '>',
        ];
        $changes = [
            [new Location(1, 1, 2, 3), "123\n345\n567"],
            [new Location(2, 4, 4, 4), '123'],
            [new Location(6, 8, 4, 4), "123\n345"],
            [new Location(11, 12, 4, 3, 2), "123\n345\n567"],
            [new Location(12, 12, 5, 2, 2), null],
            [new Location(14, 16, 4, 3, 2), '123'],
            [new Location(PHP_INT_MAX, PHP_INT_MAX), "added line a\n"],
            [new Location(PHP_INT_MAX, PHP_INT_MAX), 'added line b'],
            [new Location(PHP_INT_MIN, PHP_INT_MIN), "added line c\n"],
            [new Location(PHP_INT_MIN, PHP_INT_MIN), 'added line d'],
        ];
        $editor  = new readonly class($lines) extends Editor {
            /**
             * @return array<int, string>
             */
            public function getLines(): array {
                return $this->lines;
            }
        };

        $actual   = $editor->mutate($changes);
        $expected = [
            'added line c',
            '',
            'added line d',
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
        ];

        self::assertNotSame($editor, $actual);
        self::assertEquals($lines, $editor->getLines());
        self::assertSame($expected, $actual->getLines());
    }

    public function testExtract(): void {
        $locations = [
            new Location(2, 2, 2, 5),
            new Location(2, 2, 14, 5),
            new Location(2, 2, 26, 5),
            new Location(3, 4, 17, null),
            new Location(3, 4, 17, null), // same line -> should be ignored
            new Location(6, 6, 2, 7),
            new Location(6, 6, 9, 7),
            new Location(6, 6, 16, 5),
            new Location(7, 7, 16, 5),    // no line -> should be ignored
        ];
        $lines     = [
            2 => '11111 11111 11111 11111 11111 11111',
            3 => '22222 22222 22222 22222 22222 22222',
            4 => '33333 33333 33333 33333 33333 33333',
            5 => '44444 44444 44444 44444 44444 44444',
            6 => '55555 55555 55555 55555 55555 55555',
        ];
        $expected  = [
            '111 1 111 1 111 1',
            ' 22222 22222 22222',
            '33333 33333 33333 33333 33333 33333',
            '555 55555 55555 555',
        ];
        $editor    = new readonly class($lines) extends Editor {
            /**
             * @return array<int, string>
             */
            public function getLines(): array {
                return $this->lines;
            }
        };

        $actual = $editor->extract($locations);

        self::assertNotSame($editor, $actual);
        self::assertEquals($lines, $editor->getLines());
        self::assertSame($expected, $actual->getLines());
    }
}
