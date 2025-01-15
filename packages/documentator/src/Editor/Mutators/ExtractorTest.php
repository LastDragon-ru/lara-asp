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

        self::assertEquals($expected, $extractor($lines, $locations, $startLine));
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
