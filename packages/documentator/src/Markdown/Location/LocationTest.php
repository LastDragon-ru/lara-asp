<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Location;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Location::class)]
final class LocationTest extends TestCase {
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
}
