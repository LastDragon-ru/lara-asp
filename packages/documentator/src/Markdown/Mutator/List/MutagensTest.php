<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\List;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Exceptions\LocationsCannotBeMerged;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Exceptions\MutagensCannotBeMerged;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Extract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Finalize;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Mutagens::class)]
final class MutagensTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderIsMergeable')]
    public function testIsMergeable(bool $expected, Location $a, Location $b): void {
        $mutagens = new class() extends Mutagens {
            #[Override]
            public function isMergeable(Location $a, Location $b): bool {
                return parent::isMergeable($a, $b);
            }
        };

        self::assertSame($expected, $mutagens->isMergeable($a, $b));
    }

    #[DataProvider('dataProviderMerge')]
    public function testMerge(Location|Exception $expected, Location $a, Location $b): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $mutagens = new class() extends Mutagens {
            #[Override]
            public function merge(Location $a, Location $b): Location {
                return parent::merge($a, $b);
            }
        };

        self::assertEquals($expected, $mutagens->merge($a, $b));
    }

    #[DataProvider('dataProviderPosition')]
    public function testPosition(Position $expected, Location $a, Location $b): void {
        $mutagens = new class() extends Mutagens {
            #[Override]
            public function position(Location $a, Location $b): Position {
                return parent::position($a, $b);
            }
        };

        self::assertSame($expected, $mutagens->position($a, $b));
    }

    /**
     * @param list<Zone>|ZoneDefault $zones
     */
    #[DataProvider('dataProviderZone')]
    public function testZone(Zone|ZoneDefault|null $expected, array|ZoneDefault $zones, Location $location): void {
        $mutagens = new class($zones) extends Mutagens {
            #[Override]
            public function zone(Location $location): Zone|ZoneDefault|null {
                return parent::zone($location);
            }
        };

        self::assertEquals($expected, $mutagens->zone($location));
    }

    /**
     * @param list<Zone>|ZoneDefault|Exception $expected
     * @param list<Zone>|ZoneDefault           $zones
     */
    #[DataProvider('dataProviderAdd')]
    public function testAdd(
        array|ZoneDefault|Exception $expected,
        array|ZoneDefault $zones,
        Replace|Delete|Extract $mutagen,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $mutagens = new class($zones) extends Mutagens {
            /**
             * @return list<Zone>|ZoneDefault
             */
            public function getZones(): array|ZoneDefault {
                return $this->zones;
            }
        };

        $mutagens->add($mutagen);

        $actual = $mutagens->getZones();

        self::assertEquals($expected, $actual);
    }

    /**
     * @param list<Zone>|ZoneDefault $zones
     */
    #[DataProvider('dataProviderIsIgnored')]
    public function testIsIgnored(bool $expected, array|ZoneDefault $zones, Location $location): void {
        $mutagens = new Mutagens($zones);
        $actual   = $mutagens->isIgnored($location);

        self::assertSame($expected, $actual);
    }

    /**
     * @param list<Zone>|ZoneDefault $zones
     */
    #[DataProvider('dataProviderIsEmpty')]
    public function testIsEmpty(bool $expected, array|ZoneDefault $zones): void {
        $mutagens = new Mutagens($zones);
        $actual   = $mutagens->isEmpty();

        self::assertSame($expected, $actual);
    }

    /**
     * @param list<array{?Location, array<array-key, array{Location, ?string}>}> $expected
     * @param list<Zone>|ZoneDefault                                             $zones
     */
    #[DataProvider('dataProviderGetChanges')]
    public function testGetChanges(array $expected, array|ZoneDefault $zones): void {
        $mutagens = new Mutagens($zones);
        $actual   = [];

        foreach ($mutagens->getChanges() as $location => $changes) {
            $actual[] = [$location, $changes];
        }

        self::assertEquals($expected, $actual);
    }

    public function testGetFinalizers(): void {
        $mutagens = new Mutagens(new ZoneDefault([
            new Delete(new Location(1, 3)),
        ]));
        $finalize = new Finalize(static function (): void {
            // empty
        });

        $mutagens->add($finalize);

        self::assertSame([$finalize], $mutagens->getFinalizers());
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      list<array{?Location, array<array-key, array{Location, ?string}>}>,
     *      list<Zone>|ZoneDefault,
     *      }>
     */
    public static function dataProviderGetChanges(): array {
        $aLocation = new Location(1, 3);
        $bLocation = new Location(4, 4);

        return [
            'empty'   => [
                [],
                [],
            ],
            'default' => [
                [
                    [
                        null,
                        [
                            [$aLocation, null],
                            [$bLocation, 'text'],
                        ],
                    ],
                ],
                new ZoneDefault([
                    new Delete($aLocation),
                    new Replace($bLocation, 'text'),
                ]),
            ],
            'zone'    => [
                [
                    [
                        new Location(1, 3, 10),
                        [
                            [new Location(0, 2, 5, 5), null],
                            [new Location(1, 1), 'text'],
                        ],
                    ],
                    [
                        new Location(4, 4),
                        [
                            [new Location(0, 0), null],
                            [new Location(0, 0), 'text'],
                        ],
                    ],
                ],
                [
                    new Zone(new Location(1, 3, 10), [
                        new Delete(new Location(1, 3, 15, 5)),
                        new Replace(new Location(2, 2), 'text'),
                    ]),
                    new Zone(new Location(4, 4), [
                        new Delete(new Location(4, 4)),
                        new Replace(new Location(4, 4), 'text'),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{bool, list<Zone>|ZoneDefault}>
     */
    public static function dataProviderIsEmpty(): array {
        return [
            'empty'             => [
                true,
                [
                    // empty
                ],
            ],
            'default empty'     => [
                true,
                new ZoneDefault(),
            ],
            'default not empty' => [
                false,
                new ZoneDefault([
                    new Delete(new Location(1, 3)),
                ]),
            ],
            'zone'              => [
                false,
                [
                    new Zone(new Location(1, 5), [
                        new Delete(new Location(1, 3)),
                    ]),
                ],
            ],
            'zone empty'        => [
                false,
                [
                    new Zone(new Location(3, 5)),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{bool, list<Zone>|ZoneDefault, Location}>
     */
    public static function dataProviderIsIgnored(): array {
        return [
            'empty'        => [
                false,
                [
                    // empty
                ],
                new Location(1, 2),
            ],
            'default'      => [
                false,
                new ZoneDefault(),
                new Location(1, 2),
            ],
            'inside zone'  => [
                false,
                [
                    new Zone(new Location(1, 5)),
                ],
                new Location(1, 2),
            ],
            'outside zone' => [
                true,
                [
                    new Zone(new Location(3, 5)),
                ],
                new Location(1, 2),
            ],
            'wider'        => [
                false,
                [
                    new Zone(new Location(3, 5)),
                ],
                new Location(1, 6),
            ],
            'same'         => [
                false,
                [
                    new Zone(new Location(1, 6)),
                ],
                new Location(1, 6),
            ],
        ];
    }

    /**
     * @return array<string, array{bool, Location, Location}>
     */
    public static function dataProviderIsMergeable(): array {
        return [
            'yes'                       => [true, new Location(1, 2), new Location(1, 2)],
            'nope (`startLinePadding`)' => [false, new Location(1, 2), new Location(1, 2, startLinePadding: 2)],
            'nope (`internalPadding`)'  => [false, new Location(1, 2, internalPadding: 1), new Location(1, 2)],
        ];
    }

    /**
     * @return array<string, array{Location|Exception, Location, Location}>
     */
    public static function dataProviderMerge(): array {
        return [
            'not mergeable'    => [
                new LocationsCannotBeMerged([new Location(1, 2)]),
                new Location(1, 2),
                new Location(1, 2, internalPadding: 1),
            ],
            'no length/offset' => [
                new Location(1, 5),
                new Location(1, 2),
                new Location(4, 5),
            ],
            'length/offset'    => [
                new Location(1, 5, 2, 10, 1, 1),
                new Location(4, 5, 1, 10, 1, 1),
                new Location(1, 2, 2, 5, 1, 1),
            ],
            'length'           => [
                new Location(1, 5, 0, 10),
                new Location(1, 2, 0, 15),
                new Location(4, 5, 0, 10),
            ],
            'match'            => [
                new Location(1, 2, 0, 15),
                new Location(1, 2, 0, 15),
                new Location(1, 2, 5, 10),
            ],
        ];
    }

    /**
     * @return array<string, array{Position, Location, Location}>
     */
    public static function dataProviderPosition(): array {
        return [
            'Before'                           => [
                Position::Before,
                new Location(4, 5),
                new Location(1, 2),
            ],
            'Touch Before'                     => [
                Position::TouchStart,
                new Location(4, 5),
                new Location(1, 3),
            ],
            'Touch Before (offset + length)'   => [
                Position::TouchStart,
                new Location(3, 5, 10),
                new Location(1, 3, 0, 9),
            ],
            'Touch Before (insert)'            => [
                Position::TouchStart,
                new Location(1, 10, 1, 5),
                (new Location(1, 10, 1, 5))->before(),
            ],
            'Touch Before (insert; same line)' => [
                Position::TouchStart,
                new Location(1, 1, 1, 5),
                (new Location(1, 1, 1, 5))->before(),
            ],
            'Touch Before (insert - location)' => [
                Position::TouchStart,
                (new Location(1, 10, 1, 5))->after(),
                new Location(1, 10, 1, 5),
            ],
            'Before (offset)'                  => [
                Position::Before,
                new Location(4, 5, 1),
                new Location(1, 3),
            ],
            'Before (length)'                  => [
                Position::Before,
                new Location(4, 5),
                new Location(1, 3, 0, 10),
            ],
            'Before (intersect)'               => [
                Position::Intersect,
                new Location(3, 5, 0, 10),
                new Location(1, 3),
            ],
            'Before (before)'                  => [
                Position::Before,
                new Location(3, 5, 10),
                new Location(1, 3, 0, 8),
            ],
            'Before (all line)'                => [
                Position::Intersect,
                new Location(3, 5, 0),
                new Location(1, 3, 12),
            ],
            'After'                            => [
                Position::After,
                new Location(1, 2),
                new Location(4, 5),
            ],
            'Touch After'                      => [
                Position::TouchEnd,
                new Location(1, 3),
                new Location(4, 5),
            ],
            'After (offset)'                   => [
                Position::After,
                new Location(1, 3),
                new Location(4, 5, 1),
            ],
            'After (length)'                   => [
                Position::After,
                new Location(1, 3, 0, 10),
                new Location(4, 5),
            ],
            'After (intersect)'                => [
                Position::Intersect,
                new Location(1, 3, 0, 10),
                new Location(3, 5),
            ],
            'After (touch)'                    => [
                Position::TouchEnd,
                new Location(1, 3, 0, 10),
                new Location(3, 5, 11),
            ],
            'After (after)'                    => [
                Position::After,
                new Location(1, 3, 0, 10),
                new Location(3, 5, 12),
            ],
            'After (all line)'                 => [
                Position::Intersect,
                new Location(1, 3, 0),
                new Location(3, 5, 12),
            ],
            'After (same line + touch)'        => [
                Position::TouchEnd,
                new Location(1, 1, 11, 6),
                new Location(1, 1, 17, 4),
            ],
            'Touch After (location + insert)'  => [
                Position::TouchEnd,
                new Location(1, 10, 1, 5),
                (new Location(1, 10, 1, 5))->after(),
            ],
            'Touch After (before + location)'  => [
                Position::TouchEnd,
                (new Location(1, 10, 1, 5))->before(),
                new Location(1, 10, 1, 5),
            ],
            'Inside'                           => [
                Position::Inside,
                new Location(1, 10),
                new Location(2, 3),
            ],
            'Inside (last line)'               => [
                Position::Inside,
                new Location(1, 3),
                new Location(3, 3),
            ],
            'Inside (first line)'              => [
                Position::Inside,
                new Location(1, 3),
                new Location(1, 1),
            ],
            'Wrap'                             => [
                Position::Wrap,
                new Location(3, 4),
                new Location(1, 10),
            ],
            'Wrap (same line)'                 => [
                Position::Wrap,
                new Location(1, 1, 5, 5),
                new Location(1, 1, 2, 15),
            ],
            'Wrap (same start line)'           => [
                Position::Wrap,
                new Location(1, 1),
                new Location(1, 2),
            ],
            'Wrap (same end line)'             => [
                Position::Wrap,
                new Location(2, 3),
                new Location(1, 3),
            ],
            'Wrap (end)'                       => [
                Position::Wrap,
                new Location(1, 10, 0, 10),
                new Location(1, 10),
            ],
            'Wrap (start)'                     => [
                Position::Wrap,
                new Location(1, 10, 1),
                new Location(1, 10),
            ],
            'Same'                             => [
                Position::Same,
                new Location(1, 10),
                new Location(1, 10),
            ],
            'Same (length + offset)'           => [
                Position::Same,
                new Location(1, 10, 1, 5),
                new Location(1, 10, 1, 5),
            ],
            'Same (before + before)'           => [
                Position::Same,
                (new Location(1, 10, 1, 5))->before(),
                (new Location(1, 10, 1, 5))->before(),
            ],
            'Same (after + after)'             => [
                Position::Same,
                (new Location(1, 10, 1, 5))->after(),
                (new Location(1, 10, 1, 5))->after(),
            ],
            'Same (before + after)'            => [
                Position::Same,
                (new Location(1, 10, 1, 5))->before(),
                (new Location(1, 10, 1, 5))->before()->after(),
            ],
            'Same (after + before)'            => [
                Position::Same,
                (new Location(1, 10, 1, 5))->after(),
                (new Location(1, 10, 1, 5))->after()->before(),
            ],
        ];
    }

    /**
     * @return array<string, array{Zone|ZoneDefault|null, list<Zone>|ZoneDefault, Location}>
     */
    public static function dataProviderZone(): array {
        $aZone = new Zone(new Location(10, 25, 10, 25));
        $bZone = new Zone(new Location(45, 50));
        $cZone = new Zone(new Location(50, 75));
        $zones = [$aZone, $bZone, $cZone];

        return [
            'empty'   => [
                null,
                [
                    // empty
                ],
                new Location(1, 2345),
            ],
            'default' => [
                new ZoneDefault(),
                new ZoneDefault(),
                new Location(1, 2345),
            ],
            'before'  => [
                null,
                $zones,
                new Location(1, 2),
            ],
            'after'   => [
                null,
                $zones,
                new Location(80, 80),
            ],
            'touch'   => [
                $bZone,
                $zones,
                new Location(49, 49),
            ],
            'inside'  => [
                $cZone,
                $zones,
                $cZone->location,
            ],
        ];
    }

    /**
     * @return array<string, array{list<Zone>|ZoneDefault|Exception, list<Zone>|ZoneDefault, Replace|Delete|Extract}>
     */
    public static function dataProviderAdd(): array {
        return [
            'Extract / Empty'                      => [
                [
                    new Zone(new Location(1, 3)),
                ],
                [],
                new Extract(new Location(1, 3)),
            ],
            'Extract / Default'                    => [
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                ],
                new ZoneDefault([
                    new Delete(new Location(3, 3)),
                    new Delete(new Location(3, 4)),
                ]),
                new Extract(new Location(1, 3)),
            ],
            'Extract / Add After'                  => [
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                    new Zone(
                        new Location(5, 5),
                        [
                            // empty
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                ],
                new Extract(new Location(5, 5)),
            ],
            'Extract / Add Before'                 => [
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                    new Zone(
                        new Location(5, 5),
                        [
                            // empty
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(5, 5),
                        [
                            // empty
                        ],
                    ),
                ],
                new Extract(new Location(1, 3)),
            ],
            'Extract / Inside'                     => [
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                ],
                new Extract(new Location(2, 3)),
            ],
            'Extract / Same'                       => [
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                ],
                new Extract(new Location(1, 3)),
            ],
            'Extract / Wrap'                       => [
                [
                    new Zone(
                        new Location(1, 5),
                        [
                            new Delete(new Location(2, 2)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(2, 3),
                        [
                            new Delete(new Location(2, 2)),
                        ],
                    ),
                ],
                new Extract(new Location(1, 5)),
            ],
            'Extract / Intersect (simple)'         => [
                [
                    new Zone(
                        new Location(1, 5),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                ],
                new Extract(new Location(1, 5)),
            ],
            'Extract / Intersect'                  => [
                [
                    new Zone(
                        new Location(1, 7),
                        [
                            new Delete(new Location(3, 3)),
                            new Delete(new Location(5, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                    new Zone(
                        new Location(5, 7),
                        [
                            new Delete(new Location(5, 5)),
                        ],
                    ),
                ],
                new Extract(new Location(3, 5)),
            ],
            'Extract / TouchStart (mergeable)'     => [
                [
                    new Zone(
                        new Location(1, 5),
                        [
                            new Delete(new Location(4, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(4, 5),
                        [
                            new Delete(new Location(4, 5)),
                        ],
                    ),
                ],
                new Extract(new Location(1, 3)),
            ],
            'Extract / TouchStart (not mergeable)' => [
                [
                    new Zone(
                        new Location(1, 3, startLinePadding: 2),
                        [
                            // empty
                        ],
                    ),
                    new Zone(
                        new Location(4, 5),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(4, 5),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                ],
                new Extract(new Location(1, 3, startLinePadding: 2)),
            ],
            'Extract / TouchEnd (mergeable)'       => [
                [
                    new Zone(
                        new Location(1, 5),
                        [
                            new Delete(new Location(1, 3)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            new Delete(new Location(1, 3)),
                        ],
                    ),
                ],
                new Extract(new Location(4, 5)),
            ],
            'Extract / TouchEnd (not mergeable)'   => [
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                    new Zone(
                        new Location(4, 5, startLinePadding: 2),
                        [
                            // empty
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            new Delete(new Location(3, 3)),
                        ],
                    ),
                ],
                new Extract(new Location(4, 5, startLinePadding: 2)),
            ],
            'Delete / Empty'                       => [
                new ZoneDefault(
                    [
                        new Delete(new Location(1, 3)),
                    ],
                ),
                [],
                new Delete(new Location(1, 3)),
            ],
            'Delete / Outside'                     => [
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                ],
                new Delete(new Location(1, 5)),
            ],
            'Delete / Before'                      => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 1)),
                            new Delete(new Location(3, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(3, 5)),
                        ],
                    ),
                ],
                new Delete(new Location(1, 1)),
            ],
            'Delete / TouchStart'                  => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 2)),
                            new Delete(new Location(3, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(3, 5)),
                        ],
                    ),
                ],
                new Delete(new Location(1, 2)),
            ],
            'Delete / After'                       => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 1)),
                            new Delete(new Location(3, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 1)),
                        ],
                    ),
                ],
                new Delete(new Location(3, 5)),
            ],
            'Delete / TouchEnd'                    => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 2)),
                            new Delete(new Location(3, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 2)),
                        ],
                    ),
                ],
                new Delete(new Location(3, 5)),
            ],
            'Delete / Inside'                      => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 5)),
                        ],
                    ),
                ],
                new Delete(new Location(3, 5)),
            ],
            'Delete / Inside (conflict)'           => [
                new MutagensCannotBeMerged([/* phpunit ignores properties */]),
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 5), 'text'),
                        ],
                    ),
                ],
                new Delete(new Location(3, 5)),
            ],
            'Delete / Intersect'                   => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 4)),
                        ],
                    ),
                ],
                new Delete(new Location(3, 5)),
            ],
            'Delete / Intersect (multiple)'        => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 9)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 4)),
                            new Delete(new Location(6, 7)),
                        ],
                    ),
                ],
                new Delete(new Location(3, 9)),
            ],
            'Delete / Intersect (conflict)'        => [
                new MutagensCannotBeMerged([/* phpunit ignores properties */]),
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 4), 'text'),
                        ],
                    ),
                ],
                new Delete(new Location(3, 5)),
            ],
            'Delete / Wrap'                        => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(2, 6)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(3, 4), 'text'),
                        ],
                    ),
                ],
                new Delete(new Location(2, 6)),
            ],
            'Delete / Wrap (start same)'           => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(3, 6)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(3, 4), 'text'),
                        ],
                    ),
                ],
                new Delete(new Location(3, 6)),
            ],
            'Delete / Wrap (end same)'             => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(2, 4)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(3, 4), 'text'),
                        ],
                    ),
                ],
                new Delete(new Location(2, 4)),
            ],
            'Replace / Empty'                      => [
                new ZoneDefault(
                    [
                        new Replace(new Location(1, 3), 'text'),
                    ],
                ),
                [],
                new Replace(new Location(1, 3), 'text'),
            ],
            'Replace / Outside'                    => [
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 3),
                        [
                            // empty
                        ],
                    ),
                ],
                new Replace(new Location(1, 5), 'text'),
            ],
            'Replace / Before'                     => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 1), 'text'),
                            new Replace(new Location(3, 5), 'text'),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(3, 5), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(1, 1), 'text'),
            ],
            'Replace / TouchStart'                 => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 2), 'text'),
                            new Replace(new Location(3, 5), 'text'),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(3, 5), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(1, 2), 'text'),
            ],
            'Replace / After'                      => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 1), 'text'),
                            new Replace(new Location(3, 5), 'text'),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 1), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(3, 5), 'text'),
            ],
            'Replace / TouchEnd'                   => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 2), 'text'),
                            new Replace(new Location(3, 5), 'text'),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 2), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(3, 5), 'text'),
            ],
            'Replace / Inside'                     => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 5)),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Delete(new Location(1, 5)),
                        ],
                    ),
                ],
                new Replace(new Location(3, 5), 'text'),
            ],
            'Replace / Inside (conflict)'          => [
                new MutagensCannotBeMerged([/* phpunit ignores properties */]),
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 5), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(3, 5), 'text'),
            ],
            'Replace / Intersect'                  => [
                new MutagensCannotBeMerged([/* phpunit ignores properties */]),
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 4), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(3, 5), 'text'),
            ],
            'Replace / Wrap'                       => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 5), 'new text'),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(2, 4), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(1, 5), 'new text'),
            ],
            'Replace / Wrap (start)'               => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 5), 'new text'),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 4), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(1, 5), 'new text'),
            ],
            'Replace / Wrap (end)'                 => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 5), 'new text'),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(2, 5), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(1, 5), 'new text'),
            ],
            'Replace / Same'                       => [
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 5), 'new text'),
                        ],
                    ),
                ],
                [
                    new Zone(
                        new Location(1, 9),
                        [
                            new Replace(new Location(1, 5), 'text'),
                        ],
                    ),
                ],
                new Replace(new Location(1, 5), 'new text'),
            ],
        ];
    }
    //</editor-fold>
}
