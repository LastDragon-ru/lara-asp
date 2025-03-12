<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\List;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Exceptions\LocationsCannotBeMerged;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Exceptions\LocationsUnhandledPosition;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Exceptions\MutagensCannotBeMerged;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Extract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Finalize;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use Traversable;

use function array_merge;
use function array_splice;
use function count;
use function max;
use function min;

use const PHP_INT_MAX;

/**
 * @internal
 */
class Mutagens {
    /**
     * @var list<Finalize>
     */
    private array $finalizers = [];

    public function __construct(
        /**
         * @var list<Zone>|ZoneDefault
         */
        protected array|ZoneDefault $zones = [],
    ) {
        // empty
    }

    /**
     * @return Traversable<?Location, array<array-key, array{iterable<mixed, Coordinate>, ?string}>>
     */
    public function getChanges(): Traversable {
        if ($this->zones instanceof ZoneDefault) {
            yield null => $this->changes($this->zones->mutagens);
        } else {
            foreach ($this->zones as $zone) {
                yield $zone->location => $this->changes($zone->mutagens);
            }
        }

        yield from [];
    }

    /**
     * @param list<Replace|Delete> $mutagens
     *
     * @return array<array-key, array{iterable<mixed, Coordinate>, ?string}>
     */
    private function changes(array $mutagens): array {
        $changes = [];

        foreach ($mutagens as $mutagen) {
            $changes[] = match (true) {
                $mutagen instanceof Replace => [$mutagen->location, $mutagen->string],
                default                     => [$mutagen->location, null],
            };
        }

        return $changes;
    }

    /**
     * @return list<Finalize>
     */
    public function getFinalizers(): array {
        return $this->finalizers;
    }

    public function isEmpty(): bool {
        return $this->zones === []
            || ($this->zones instanceof ZoneDefault && $this->zones->mutagens === []);
    }

    public function isIgnored(Location $location): bool {
        // Default or empty?
        if ($this->zones instanceof ZoneDefault || $this->zones === []) {
            return false;
        }

        // Intersect with any zone?
        $ignored = true;

        for ($i = count($this->zones) - 1; $i >= 0; $i--) {
            $position = $this->position($this->zones[$i]->location, $location);

            switch ($position) {
                case Position::Same:
                case Position::Wrap:
                case Position::Inside:
                case Position::Intersect:
                    $ignored = false;
                    break 2;
                case Position::Before:
                case Position::TouchStart:
                    // continue
                    break;
                default:
                    break 2;
            }
        }

        return $ignored;
    }

    public function add(Replace|Delete|Extract|Finalize $mutagen): void {
        if ($mutagen instanceof Replace) {
            $this->addReplace($mutagen);
        } elseif ($mutagen instanceof Delete) {
            $this->addDelete($mutagen);
        } elseif ($mutagen instanceof Extract) {
            $this->addExtract($mutagen);
        } else {
            $this->addFinalize($mutagen);
        }
    }

    /**
     * @param list<Replace|Delete|Extract|Finalize> $mutagens
     */
    public function addAll(array $mutagens): void {
        foreach ($mutagens as $mutagen) {
            $this->add($mutagen);
        }
    }

    private function addExtract(Extract $mutagen): void {
        if ($this->zones === []) {
            // No zone yet? Create a new one.
            $this->zones[] = new Zone($mutagen->location);
        } elseif ($this->zones instanceof ZoneDefault) {
            // Default zone only? Create new and re-add all mutagens from the default.
            $mutagens    = $this->zones->mutagens;
            $this->zones = [new Zone($mutagen->location)];

            $this->addAll($mutagens);
        } else {
            // Add new/Merge with existing zone.
            $index    = count($this->zones) - 1;
            $location = $mutagen->location;
            $mutagens = [];

            for (; $index >= 0; $index--) {
                $zone     = $this->zones[$index];
                $position = $this->position($zone->location, $location);

                switch ($position) {
                    case Position::Before:
                        // Check previous
                        break;
                    case Position::TouchStart:
                        // Merge if possible, check previous otherwise
                        if ($this->isMergeable($zone->location, $location)) {
                            $location = $this->merge($zone->location, $location);
                            $mutagens = array_merge($mutagens, $zone->mutagens);

                            array_splice($this->zones, $index, 1);
                        }

                        break;
                    case Position::Same:
                    case Position::Inside:
                        // Ignore.
                        $location = null;
                        $mutagens = [];
                        break;
                    case Position::Wrap:
                    case Position::Intersect:
                        // Merge and then check previous
                        $location = $this->merge($zone->location, $location);
                        $mutagens = array_merge($zone->mutagens, $mutagens);

                        array_splice($this->zones, $index, 1);
                        break;
                    case Position::TouchEnd:
                        // Merge if possible, add new zone otherwise
                        if ($this->isMergeable($zone->location, $location)) {
                            $location = $this->merge($zone->location, $location);
                            $mutagens = array_merge($zone->mutagens, $mutagens);

                            array_splice($this->zones, $index, 1);
                        } else {
                            array_splice($this->zones, $index + 1, 0, [new Zone($location, $mutagens)]);

                            $location = null;
                            $mutagens = [];
                        }

                        break;
                    case Position::After:
                        // Add new zone
                        array_splice($this->zones, $index + 1, 0, [new Zone($location, $mutagens)]);

                        $location = null;
                        $mutagens = [];
                        break;
                    default:
                        throw new LocationsUnhandledPosition($position, [$zone->location, $location]);
                }

                if ($location === null) {
                    break;
                }
            }

            if ($location !== null) {
                array_splice($this->zones, $index, 0, [new Zone($location, $mutagens)]);
            }
        }
    }

    private function addDelete(Delete $delete): void {
        // Empty?
        if ($this->zones === []) {
            $this->zones = new ZoneDefault([$delete]);

            return;
        }

        // Zone?
        $zone = $this->zone($delete->location);

        if ($zone === null) {
            return;
        }

        // Add
        $index = count($zone->mutagens) - 1;

        for (; $index >= 0; $index--) {
            $mutagen  = $zone->mutagens[$index];
            $position = $this->position($mutagen->location, $delete->location);

            switch ($position) {
                case Position::Before:
                case Position::TouchStart:
                    // Check previous
                    break;
                case Position::Inside:
                    // Skip
                    if ($mutagen instanceof Delete) {
                        $delete = null;
                    } else {
                        throw new MutagensCannotBeMerged([$mutagen, $delete]);
                    }
                    break;
                case Position::Wrap:
                case Position::Same:
                    // Remove existing
                    array_splice($zone->mutagens, $index, 1);
                    break;
                case Position::Intersect:
                    // Merge if possible
                    if ($mutagen instanceof Delete) {
                        $delete = new Delete($this->merge($mutagen->location, $delete->location));

                        array_splice($zone->mutagens, $index, 1);
                    } else {
                        throw new MutagensCannotBeMerged([$mutagen, $delete]);
                    }
                    break;
                case Position::TouchEnd:
                case Position::After:
                    // Add
                    array_splice($zone->mutagens, $index + 1, 0, [$delete]);

                    $delete = null;
                    break;
                default:
                    throw new LocationsUnhandledPosition($position, [$mutagen->location, $delete->location]);
            }

            if ($delete === null) {
                break;
            }
        }

        if ($delete !== null) {
            array_splice($zone->mutagens, $index, 0, [$delete]);
        }
    }

    private function addReplace(Replace $replace): void {
        // Empty?
        if ($this->zones === []) {
            $this->zones = new ZoneDefault([$replace]);

            return;
        }

        // Zone?
        $zone = $this->zone($replace->location);

        if ($zone === null) {
            return;
        }

        // Add
        $index = count($zone->mutagens) - 1;

        for (; $index >= 0; $index--) {
            $mutagen  = $zone->mutagens[$index];
            $position = $this->position($mutagen->location, $replace->location);

            switch ($position) {
                case Position::Before:
                case Position::TouchStart:
                    // Check previous
                    break;
                case Position::Inside:
                    // Skip
                    if ($mutagen instanceof Delete) {
                        $replace = null;
                    } else {
                        throw new MutagensCannotBeMerged([$mutagen, $replace]);
                    }
                    break;
                case Position::Same:
                case Position::Wrap:
                    // Remove existing
                    array_splice($zone->mutagens, $index, 1);
                    break;
                case Position::Intersect:
                    // Impossible
                    throw new MutagensCannotBeMerged([$mutagen, $replace]);
                case Position::TouchEnd:
                case Position::After:
                    // Add
                    array_splice($zone->mutagens, $index + 1, 0, [$replace]);

                    $replace = null;
                    break;
                default:
                    throw new LocationsUnhandledPosition($position, [$mutagen->location, $replace->location]);
            }

            if ($replace === null) {
                break;
            }
        }

        if ($replace !== null) {
            array_splice($zone->mutagens, $index, 0, [$replace]);
        }
    }

    private function addFinalize(Finalize $finalize): void {
        $this->finalizers[] = $finalize;
    }

    protected function zone(Location $location): Zone|ZoneDefault|null {
        $zone = null;

        if ($this->zones instanceof ZoneDefault) {
            $zone = $this->zones;
        } else {
            for ($i = count($this->zones) - 1; $i >= 0; $i--) {
                $position = $this->position($this->zones[$i]->location, $location);

                switch ($position) {
                    case Position::Same:
                    case Position::Inside:
                        $zone = $this->zones[$i];
                        break 2;
                    case Position::Before:
                    case Position::TouchStart:
                        // continue
                        break;
                    default:
                        break 2;
                }
            }
        }

        return $zone;
    }

    protected function merge(Location $a, Location $b): Location {
        if (!$this->isMergeable($a, $b)) {
            throw new LocationsCannotBeMerged([$a, $b]);
        }

        $startLine = min($a->startLine, $b->startLine);
        $endLine   = max($a->endLine, $b->endLine);
        $offset    = match (true) {
            $a->startLine < $b->startLine => $a->offset,
            $a->startLine > $b->startLine => $b->offset,
            default                       => min($a->offset, $b->offset),
        };
        $length = match (true) {
            $a->endLine > $b->endLine                  => $a->length,
            $a->endLine < $b->endLine                  => $b->length,
            $a->length !== null && $b->length !== null => max($a->length, $b->length),
            default                                    => null,
        };
        $startLinePadding = $a->startLinePadding;
        $internalPadding  = $a->internalPadding;

        return new Location(
            $startLine,
            $endLine,
            $offset,
            $length,
            $startLinePadding,
            $internalPadding,
        );
    }

    protected function isMergeable(Location $a, Location $b): bool {
        return $a->startLinePadding === $b->startLinePadding
            && $a->internalPadding === $b->internalPadding;
    }

    protected function position(Location $a, Location $b): Position {
        $position = null;

        if ($a->startLine === $b->startLine && $a->endLine === $b->endLine) {
            $position = $a->offset !== $b->offset || $a->length !== $b->length
                ? $this->pos($a->offset, $this->end($a), $b->offset, $this->end($b))
                : Position::Same;
        } else {
            $position = $this->pos($a->startLine, $a->endLine, $b->startLine, $b->endLine);

            switch ($position) {
                case Position::TouchStart:
                    if ($b->length !== null || $a->offset !== 0) {
                        $position = Position::Before;
                    }
                    break;
                case Position::TouchEnd:
                    if ($a->length !== null || $b->offset !== 0) {
                        $position = Position::After;
                    }
                    break;
                case Position::Intersect:
                    if ($b->startLine === $a->startLine) {
                        $position = $this->pos($a->offset, PHP_INT_MAX, $b->offset, PHP_INT_MAX);
                    } elseif ($b->startLine === $a->endLine) {
                        $position = $this->pos(0, $a->length ?? PHP_INT_MAX, $b->offset, PHP_INT_MAX);
                    } elseif ($b->endLine === $a->startLine) {
                        $position = $this->pos($a->offset, PHP_INT_MAX, 0, $b->length ?? PHP_INT_MAX);
                    } elseif ($b->endLine === $a->endLine) {
                        $position = $this->pos(0, $a->length ?? PHP_INT_MAX, 0, $b->length ?? PHP_INT_MAX);
                    } else {
                        // as is
                    }

                    if ($position === Position::Inside || $position === Position::Wrap) {
                        $position = Position::Intersect;
                    }
                    break;
                default:
                    // as is
                    break;
            }
        }

        return $position;
    }

    private function end(Location $location): int {
        $end = $location->length ?? PHP_INT_MAX;

        if ($location->startLine === $location->endLine && $end !== PHP_INT_MAX) {
            $end = min(PHP_INT_MAX, $location->offset + $end - 1);
        }

        return $end;
    }

    private function pos(int $aStart, int $aEnd, int $bStart, int $bEnd): Position {
        if ($bEnd === $aStart - 1) {
            return Position::TouchStart;
        } elseif ($bEnd < $aStart) {
            return Position::Before;
        } elseif ($bStart - 1 === $aEnd) {
            return Position::TouchEnd;
        } elseif ($bStart > $aEnd) {
            return Position::After;
        } elseif ($aStart <= $bStart && $bEnd <= $aEnd) {
            return Position::Inside;
        } elseif ($aStart >= $bStart && $bEnd >= $aEnd) {
            return Position::Wrap;
        } else {
            return Position::Intersect;
        }
    }
}
