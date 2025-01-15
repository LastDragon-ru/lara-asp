<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Mutators;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;

use function array_key_last;
use function implode;
use function mb_substr;
use function usort;

readonly class Extractor extends Base {
    /**
     * @param list<string>                                 $lines
     * @param iterable<mixed, iterable<mixed, Coordinate>> $locations
     *
     * @return list<string>
     */
    public function __invoke(array $lines, iterable $locations, int $startLine = 0): array {
        $prepared = $this->unpack($locations);
        $prepared = $this->prepare($prepared);
        $result   = [];

        foreach ($prepared as $index => $coordinates) {
            // Line exist?
            $number = $index - $startLine;

            if (!isset($lines[$number])) {
                continue;
            }

            // Extract
            $line = [];

            foreach ($coordinates as $coordinate) {
                $line[] = mb_substr($lines[$number], $coordinate->offset, $coordinate->length);
            }

            $result[] = implode(' ', $line);
        }

        return $result;
    }

    /**
     * @param iterable<mixed, iterable<mixed, Coordinate>> $locations
     *
     * @return list<Coordinate>
     */
    protected function unpack(iterable $locations): array {
        $prepared = [];

        foreach ($locations as $location) {
            foreach ($location as $coordinate) {
                $prepared[] = $coordinate;
            }
        }

        usort($prepared, $this->compare(...));

        return $prepared;
    }

    /**
     * @param list<Coordinate> $coordinates
     *
     * @return array<int, array<int, Coordinate>>
     */
    protected function prepare(array $coordinates): array {
        $lines = [];

        foreach ($coordinates as $coordinate) {
            if ($this->isOverlapped($lines, $coordinate, $key)) {
                // Coordinates are overlapped -> merge
                $overlapped                     = $lines[$coordinate->line][$key] ?? $coordinate;
                $lines[$coordinate->line][$key] = $this->merge($overlapped, $coordinate);
            } elseif (isset($lines[$coordinate->line])) {
                // Coordinates may touch each other -> merge if yes.
                $key      = array_key_last($lines[$coordinate->line]);
                $previous = $lines[$coordinate->line][$key];

                if ($previous->length !== null && $previous->offset + $previous->length === $coordinate->offset) {
                    $lines[$coordinate->line][$key] = $this->merge($previous, $coordinate);
                } else {
                    $lines[$coordinate->line][] = $coordinate;
                }
            } else {
                // Just add
                $lines[$coordinate->line][] = $coordinate;
            }
        }

        return $lines;
    }

    private function merge(Coordinate $a, Coordinate $b): Coordinate {
        return new Coordinate(
            $a->line,
            $a->offset,
            $b->length !== null && $a->length !== null
                ? ($a->length + $b->length) - (($a->offset + $a->length) - $b->offset)
                : null,
        );
    }
}
