<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

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

    public function testExtract(): void {
        $startLine = 2;
        $locations = [
            new Location(0 + $startLine, 0 + $startLine, 2, 5),
            new Location(0 + $startLine, 0 + $startLine, 14, 5),
            new Location(0 + $startLine, 0 + $startLine, 26, 5),
            new Location(1 + $startLine, 2 + $startLine, 17, null),
            new Location(1 + $startLine, 2 + $startLine, 17, null), // same line -> should be ignored
            new Location(4 + $startLine, 4 + $startLine, 2, 7),
            new Location(4 + $startLine, 4 + $startLine, 9, 7),
            new Location(4 + $startLine, 4 + $startLine, 16, 5),
            new Location(5 + $startLine, 5 + $startLine, 16, 5),    // no line -> should be ignored
        ];
        $lines     = [
            '11111 11111 11111 11111 11111 11111',
            '22222 22222 22222 22222 22222 22222',
            '33333 33333 33333 33333 33333 33333',
            '44444 44444 44444 44444 44444 44444',
            '55555 55555 55555 55555 55555 55555',
        ];
        $expected  = [
            '111 1 111 1 111 1',
            ' 22222 22222 22222',
            '33333 33333 33333 33333 33333 33333',
            '555 55555 55555 555',
        ];
        $editor    = new readonly class($lines, $startLine) extends Editor {
            /**
             * @return list<string>
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
