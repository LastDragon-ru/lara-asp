<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Locations;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function iterator_to_array;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(Location::class)]
final class LocationTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testGetIterator(): void {
        self::assertEquals(
            [
                new Coordinate(1, 0, null, 0),
            ],
            iterator_to_array(new Location(1, 1), false),
        );
        self::assertEquals(
            [
                new Coordinate(1, 10, 10, 0),
            ],
            iterator_to_array(new Location(1, 1, 10, 10), false),
        );
        self::assertEquals(
            [
                new Coordinate(1, 12, 10, 2),
            ],
            iterator_to_array(new Location(1, 1, 10, 10, 2, 4), false),
        );
        self::assertEquals(
            [
                new Coordinate(1, 12, null, 2),
                new Coordinate(2, 2, null, 2),
                new Coordinate(3, 2, 10, 2),
            ],
            iterator_to_array(new Location(1, 3, 10, 10, 2, null), false),
        );
        self::assertEquals(
            [
                new Coordinate(1, 12, null, 2),
                new Coordinate(2, 4, null, 4),
                new Coordinate(3, 4, 10, 4),
            ],
            iterator_to_array(new Location(1, 3, 10, 10, 2, 4), false),
        );
    }

    #[DataProvider('dataProviderMove')]
    public function testMove(Location $expected, Location $location, int $move): void {
        self::assertEquals($expected, $location->move($move));
    }

    #[DataProvider('dataProviderMoveOffset')]
    public function testMoveOffset(Location $expected, Location $location, int $move): void {
        self::assertEquals($expected, $location->moveOffset($move));
    }

    #[DataProvider('dataProviderMoveLength')]
    public function testMoveLength(Location $expected, Location $location, int $move): void {
        self::assertEquals($expected, $location->moveLength($move));
    }

    public function testBefore(): void {
        self::assertEquals(
            new Location(3, 3, 15, 0, 2, 4),
            (new Location(3, 5, 15, 5, 2, 4))->before(),
        );
    }

    public function testAfter(): void {
        self::assertEquals(
            new Location(5, 5, 5, 0, 4, null),
            (new Location(3, 5, 15, 5, 2, 4))->after(),
        );
        self::assertEquals(
            new Location(5, 5, 5, 0, 2, null),
            (new Location(3, 5, 15, 5, 2))->after(),
        );
        self::assertEquals(
            new Location(5, 5, PHP_INT_MAX, 0),
            (new Location(3, 5, 15))->after(),
        );
        self::assertEquals(
            new Location(3, 3, PHP_INT_MAX, 0),
            (new Location(3, 3, 15))->after(),
        );
        self::assertEquals(
            new Location(3, 3, 20, 0),
            (new Location(3, 3, 15, 5))->after(),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{Location, Location, int}>
     */
    public static function dataProviderMove(): array {
        return [
            'no move'              => [
                new Location(1, 1, 10, 10),
                new Location(1, 1, 10, 10),
                0,
            ],
            'positive'             => [
                new Location(6, 6, 10, 10),
                new Location(1, 1, 10, 10),
                5,
            ],
            'positive (multiline)' => [
                new Location(6, 7, 10, 10),
                new Location(1, 2, 10, 10),
                5,
            ],
            'negative'             => [
                new Location(5, 5, 10, 10),
                new Location(10, 10, 10, 10),
                -5,
            ],
            'negative (too big)'   => [
                new Location(0, 4, 10, 10),
                new Location(1, 5, 10, 10),
                -50,
            ],
            'negative (multiline)' => [
                new Location(0, 2, 10, 10),
                new Location(5, 7, 10, 10),
                -5,
            ],
        ];
    }

    /**
     * @return array<string, array{Location, Location, int}>
     */
    public static function dataProviderMoveOffset(): array {
        return [
            'no shift'             => [
                new Location(1, 1, 10, 10),
                new Location(1, 1, 10, 10),
                0,
            ],
            'positive'             => [
                new Location(1, 1, 15, 5),
                new Location(1, 1, 10, 10),
                5,
            ],
            'positive (multiline)' => [
                new Location(1, 2, 15, 10),
                new Location(1, 2, 10, 10),
                5,
            ],
            'negative'             => [
                new Location(1, 1, 5, 15),
                new Location(1, 1, 10, 10),
                -5,
            ],
            'negative (too big)'   => [
                new Location(1, 1, 0, 20),
                new Location(1, 1, 10, 10),
                -50,
            ],
            'negative (multiline)' => [
                new Location(1, 2, 5, 10),
                new Location(1, 2, 10, 10),
                -5,
            ],
        ];
    }

    /**
     * @return array<string, array{Location, Location, int}>
     */
    public static function dataProviderMoveLength(): array {
        return [
            'no shift'             => [
                new Location(1, 1, 10, 10),
                new Location(1, 1, 10, 10),
                0,
            ],
            'positive'             => [
                new Location(1, 1, 10, 15),
                new Location(1, 1, 10, 10),
                5,
            ],
            'positive (multiline)' => [
                new Location(1, 2, 10, 15),
                new Location(1, 2, 10, 10),
                5,
            ],
            'negative'             => [
                new Location(1, 1, 10, 5),
                new Location(1, 1, 10, 10),
                -5,
            ],
            'negative (too big)'   => [
                new Location(1, 1, 10, 0),
                new Location(1, 1, 10, 10),
                -50,
            ],
            'negative (multiline)' => [
                new Location(1, 2, 10, 5),
                new Location(1, 2, 10, 10),
                -5,
            ],
        ];
    }
    //</editor-fold>
}
