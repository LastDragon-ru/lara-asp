<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Mutators;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Extractor::class)]
final class ExtractorTest extends TestCase {
    public function testInvoke(): void {
        $extractor = new Extractor();
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
            2 => '111 1 111 1 111 1',
            3 => ' 22222 22222 22222',
            4 => '33333 33333 33333 33333 33333 33333',
            6 => '555 55555 55555 555',
        ];

        self::assertEquals($expected, $extractor($lines, $locations));
    }

    public function testUnpack(): void {
        $extractor = new readonly class() extends Extractor {
            /**
             * @inheritDoc
             */
            #[Override]
            public function unpack(iterable $locations): array {
                return parent::unpack($locations);
            }
        };
        $locations = [
            new Location(12, 15, 5, 10),
            new Location(10, 10, 15, 10),
            new Location(10, 10, 10, null),
        ];
        $expected  = [
            new Coordinate(10, 10, null, 0),
            new Coordinate(10, 15, 10, 0),
            new Coordinate(12, 5, null, 0),
            new Coordinate(13, 0, null, 0),
            new Coordinate(14, 0, null, 0),
            new Coordinate(15, 0, 10, 0),
        ];

        self::assertEquals($expected, $extractor->unpack($locations));
    }

    public function testPrepare(): void {
        $extractor   = new readonly class() extends Extractor {
            /**
             * @inheritDoc
             */
            #[Override]
            public function prepare(array $coordinates): array {
                return parent::prepare($coordinates);
            }
        };
        $coordinates = [
            new Coordinate(10, 10, null, 123),
            new Coordinate(10, 15, 10, 123),
            new Coordinate(12, 0, 4, 123),
            new Coordinate(12, 5, null, 123),
            new Coordinate(14, 0, null, 123),
            new Coordinate(15, 0, 10, 123),
            new Coordinate(15, 9, 15, 123),
            new Coordinate(16, 10, 10, 123),
            new Coordinate(16, 19, 15, 123),
            new Coordinate(17, 10, 10, 123),
            new Coordinate(17, 20, 15, 123),
        ];
        $expected    = [
            10 => [
                new Coordinate(10, 10, null),
            ],
            12 => [
                new Coordinate(12, 0, 4, 123),
                new Coordinate(12, 5, null, 123),
            ],
            14 => [
                new Coordinate(14, 0, null, 123),
            ],
            15 => [
                new Coordinate(15, 0, 24),
            ],
            16 => [
                new Coordinate(16, 10, 24),
            ],
            17 => [
                new Coordinate(17, 10, 25),
            ],
        ];

        self::assertEquals($expected, $extractor->prepare($coordinates));
    }
}
