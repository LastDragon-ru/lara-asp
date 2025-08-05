<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Mutators;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Extractor::class)]
final class BaseTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testCompare(): void {
        $base = new readonly class() extends Base {
            #[Override]
            public function compare(Coordinate $a, Coordinate $b): int {
                return parent::compare($a, $b);
            }
        };

        self::assertSame(-1, $base->compare(new Coordinate(1, 0, 1), new Coordinate(2, 0, 1)));
        self::assertSame(1, $base->compare(new Coordinate(2, 0, 1), new Coordinate(1, 0, 1)));
        self::assertSame(0, $base->compare(new Coordinate(1, 0, 1), new Coordinate(1, 0, 1)));
        self::assertSame(1, $base->compare(new Coordinate(1, 10, 1), new Coordinate(1, 0, 1)));
        self::assertSame(-1, $base->compare(new Coordinate(1, 0, 1), new Coordinate(1, 0, 2)));
    }

    /**
     * @param array{bool, ?int}                  $expected
     * @param array<int, array<int, Coordinate>> $lines
     */
    #[DataProvider('dataProviderIsOverlapped')]
    public function testIsOverlapped(array $expected, array $lines, Coordinate $coordinate): void {
        $base = new readonly class() extends Base {
            /**
             * @inheritDoc
             */
            #[Override]
            public function isOverlapped(
                array $coordinates,
                Coordinate $coordinate,
                ?int &$key = null,
            ): bool {
                return parent::isOverlapped(
                    $coordinates,
                    $coordinate,
                    $key,
                );
            }
        };

        self::assertSame($expected[0], $base->isOverlapped($lines, $coordinate, $key));
        self::assertSame($expected[1], $key);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{array{bool, ?int}, array<int, array<int, Coordinate>>, Coordinate}>
     */
    public static function dataProviderIsOverlapped(): array {
        return [
            'nope'                       => [
                [false, null],
                [
                    10 => [
                        new Coordinate(10, 10, null),
                    ],
                ],
                new Coordinate(11, 10, null),
            ],
            'nope (same line)'           => [
                [false, null],
                [
                    10 => [
                        new Coordinate(10, 10, 10),
                    ],
                ],
                new Coordinate(10, 20, 10),
            ],
            'overlapped'                 => [
                [true, 0],
                [
                    10 => [
                        new Coordinate(10, 10, null),
                    ],
                ],
                new Coordinate(10, 15, 10),
            ],
            'overlapped (reverse)'       => [
                [true, 0],
                [
                    10 => [
                        new Coordinate(10, 15, 10),
                    ],
                ],
                new Coordinate(10, 10, null),
            ],
            'overlapped (one character)' => [
                [true, 1],
                [
                    10 => [
                        new Coordinate(10, 0, 5),
                        new Coordinate(10, 10, 10),
                    ],
                ],
                new Coordinate(10, 19, 10),
            ],
        ];
    }
    //</editor-fold>
}
