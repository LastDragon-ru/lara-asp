<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Locations;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function iterator_to_array;

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
            iterator_to_array(new Location(1, 1)),
        );
        self::assertEquals(
            [
                new Coordinate(1, 10, 10, 0),
            ],
            iterator_to_array(new Location(1, 1, 10, 10)),
        );
        self::assertEquals(
            [
                new Coordinate(1, 12, 10, 2),
            ],
            iterator_to_array(new Location(1, 1, 10, 10, 2, 4)),
        );
        self::assertEquals(
            [
                new Coordinate(1, 12, null, 2),
                new Coordinate(2, 2, null, 2),
                new Coordinate(3, 2, 10, 2),
            ],
            iterator_to_array(new Location(1, 3, 10, 10, 2, null)),
        );
        self::assertEquals(
            [
                new Coordinate(1, 12, null, 2),
                new Coordinate(2, 4, null, 4),
                new Coordinate(3, 4, 10, 4),
            ],
            iterator_to_array(new Location(1, 3, 10, 10, 2, 4)),
        );
    }

    #[DataProvider('dataProviderMoveOffset')]
    public function testMoveOffset(Location $expected, Location $location, int $move): void {
        self::assertEquals($expected, $location->moveOffset($move));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
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
    //</editor-fold>
}
