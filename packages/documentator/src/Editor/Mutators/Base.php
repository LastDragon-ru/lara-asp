<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Mutators;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;

use const PHP_INT_MAX;

readonly class Base {
    public function __construct() {
        // empty
    }

    protected function compare(Coordinate $a, Coordinate $b): int {
        $result = $a->line <=> $b->line;
        $result = $result === 0
            ? $a->offset <=> $b->offset
            : $result;
        $result = $result === 0
            ? ($a->length ?? PHP_INT_MAX) <=> ($b->length ?? PHP_INT_MAX)
            : $result;

        return $result;
    }

    /**
     * @phpstan-assert-if-true int               $key
     *
     * @param array<int, array<int, Coordinate>> $coordinates
     */
    protected function isOverlapped(array $coordinates, Coordinate $coordinate, ?int &$key = null): bool {
        $key = null;

        foreach ($coordinates[$coordinate->line] ?? [] as $k => $c) {
            $aStart = $c->offset;
            $aEnd   = $aStart + ($c->length ?? PHP_INT_MAX) - 1;
            $bStart = $coordinate->offset;
            $bEnd   = $bStart + ($coordinate->length ?? PHP_INT_MAX) - 1;

            if (!($bEnd < $aStart || $bStart > $aEnd)) {
                $key = $k;
                break;
            }
        }

        return $key !== null;
    }
}
