<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Mutators;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Append;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Prepend;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * @internal
 */
#[CoversClass(Mutator::class)]
final class MutatorTest extends TestCase {
    public function testInvoke(): void {
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
            16 => '>',
            17 => 'm n o p',
            18 => 'q r s t',
            19 => 'u v w x',
        ];
        $changes  = [
            [new Location(1, 1, 2, 3), "123\n345\n567"],
            [new Location(2, 4, 4, 4), '123'],
            [new Location(6, 8, 4, 4), "123\n345"],
            [new Location(11, 12, 4, 3, 2), "123\n345\n567"],
            [new Location(12, 12, 5, 2, 2), null],
            [new Location(14, 16, 4, 3, 2), '123'],
            [new Location(17, 17, 0, 0), 'prefix '],
            [new Location(17, 17, PHP_INT_MAX, 0), ' suffix'],
            [new Location(18, 18, PHP_INT_MAX, 10), ' suffix'],
            [new Location(19, 19, PHP_INT_MAX, null), ' suffix'],
            [new Location(PHP_INT_MAX, PHP_INT_MAX), "added line a\n"],
            [new Location(PHP_INT_MAX, PHP_INT_MAX), 'added line b'],
            [new Location(PHP_INT_MIN, PHP_INT_MIN), "added line c\n"],
            [new Location(PHP_INT_MIN, PHP_INT_MIN), 'added line d'],
        ];
        $mutator  = new Mutator();
        $actual   = ($mutator)($lines, $changes);
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
            'prefix m n o p suffix',
            'q r s t suffix',
            'u v w x suffix',
            'added line a',
            '',
            'added line b',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testUnpack(): void {
        $mutator  = new readonly class() extends Mutator {
            /**
             * @inheritDoc
             */
            #[Override]
            public function unpack(iterable $changes): array {
                return parent::unpack($changes);
            }
        };
        $changes  = [
            [new Location(10, 10, 15, 10), 'a'],
            [new Location(10, 10, 10, null), 'b'],
            [new Location(12, 15, 5, 10), 'c'],
        ];
        $expected = [
            [iterator_to_array(new Location(12, 15, 5, 10), false), 'c'],
            [iterator_to_array(new Location(10, 10, 10, null), false), 'b'],
            [iterator_to_array(new Location(10, 10, 15, 10), false), 'a'],
        ];

        self::assertEquals($expected, $mutator->unpack($changes));
    }

    public function testCleanup(): void {
        $mutator  = new readonly class() extends Mutator {
            /**
             * @inheritDoc
             */
            #[Override]
            public function cleanup(array $changes): array {
                return parent::cleanup($changes);
            }
        };
        $changes  = [
            0  => [iterator_to_array(new Location(18, 18, 5, 10), false), 'a'],
            1  => [iterator_to_array(new Location(17, 17, 11, 10), false), 'b'],
            2  => [iterator_to_array(new Location(17, 17, 5, 10), false), 'c'],
            3  => [iterator_to_array(new Location(14, 15, 5, 10), false), 'd'],
            4  => [iterator_to_array(new Location(12, 15, 5, 10), false), 'e'],
            5  => [iterator_to_array(new Location(10, 10, 10, null), false), 'f'],
            6  => [iterator_to_array(new Location(10, 10, 15, 10), false), 'g'],
            7  => [iterator_to_array(new Location(9, 9, 39, 11), false), 'h'],
            8  => [iterator_to_array(new Location(9, 9, 50, null), false), 'i'],
            9  => [iterator_to_array(new Location(9, 9, 40, 10), false), 'j'],
            10 => [iterator_to_array(new Location(PHP_INT_MAX, PHP_INT_MAX), false), 'k'],
            11 => [iterator_to_array(new Append(), false), 'l'],
            12 => [iterator_to_array(new Location(PHP_INT_MIN, PHP_INT_MIN), false), 'm'],
            13 => [iterator_to_array(new Prepend(), false), 'n'],
        ];
        $expected = [
            12 => [iterator_to_array(new Location(PHP_INT_MIN, PHP_INT_MIN), false), 'm'],
            13 => [iterator_to_array(new Prepend(), false), 'n'],
            0  => [iterator_to_array(new Location(18, 18, 5, 10), false), 'a'],
            1  => [iterator_to_array(new Location(17, 17, 11, 10), false), 'b'],
            3  => [iterator_to_array(new Location(14, 15, 5, 10), false), 'd'],
            5  => [iterator_to_array(new Location(10, 10, 10, null), false), 'f'],
            7  => [iterator_to_array(new Location(9, 9, 39, 11), false), 'h'],
            8  => [iterator_to_array(new Location(9, 9, 50, null), false), 'i'],
            10 => [iterator_to_array(new Location(PHP_INT_MAX, PHP_INT_MAX), false), 'k'],
            11 => [iterator_to_array(new Append(), false), 'l'],
        ];

        self::assertEquals($expected, $mutator->cleanup($changes));
    }

    public function testPrepare(): void {
        $mutator  = new readonly class() extends Mutator {
            /**
             * @inheritDoc
             */
            #[Override]
            public function prepare(array $changes): array {
                return parent::prepare($changes);
            }
        };
        $changes  = [
            [iterator_to_array(new Location(PHP_INT_MIN, PHP_INT_MIN), false), "new line ea\nnew line eb"],
            [iterator_to_array(new Location(PHP_INT_MAX, PHP_INT_MAX), false), "new line aa\nnew line ab"],
            [iterator_to_array(new Location(6, 6, 5, 10, 2), false), "text aa\ntext ab"],
            [iterator_to_array(new Location(4, 5, 5, 5, 1), false), "text ba\ntext bb"],
            [iterator_to_array(new Location(2, 3, 5, null), false), 'text c'],
            [iterator_to_array(new Location(1, 1, 5, 10), false), "text da\ntext db\ntext dc"],
            [iterator_to_array(new Append(), false), "new line ba\nnew line bb"],
            [iterator_to_array(new Prepend(), false), "new line fa\nnew line fb"],
        ];
        $expected = [
            [new Coordinate(6, 7, 10, 2), ['text aa', 'text ab']],
            [new Coordinate(5, 1, 5, 1), ['text bb']],
            [new Coordinate(4, 6, null, 1), ['text ba']],
            [new Coordinate(3, 0, null, 0), []],
            [new Coordinate(2, 5, null, 0), ['text c']],
            [new Coordinate(1, 5, 10, 0), ['text da', 'text db', 'text dc']],
            [new Coordinate(PHP_INT_MIN, 0, null, 0), ['new line ea', 'new line eb']],
            [new Coordinate(PHP_INT_MIN, 0, null, 0), ['new line fa', 'new line fb']],
            [new Coordinate(PHP_INT_MAX, 0, null, 0), ['new line ba', 'new line bb']],
            [new Coordinate(PHP_INT_MAX, 0, null, 0), ['new line aa', 'new line ab']],
        ];

        self::assertEquals($expected, $mutator->prepare($changes));
    }
}
