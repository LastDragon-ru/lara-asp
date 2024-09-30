<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;
use Stringable;

use function array_merge;
use function array_push;
use function array_reverse;
use function array_slice;
use function array_splice;
use function array_values;
use function count;
use function implode;
use function mb_substr;
use function rtrim;
use function usort;

use const PHP_INT_MAX;

/**
 * @internal
 */
class Editor implements Stringable {
    public function __construct(
        /**
         * @var list<string>
         */
        private array $lines,
        private int $offset = 0,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return implode("\n", $this->lines);
    }

    /**
     * @return list<string>
     */
    public function getLines(): array {
        return $this->lines;
    }

    public function getOffset(): int {
        return $this->offset;
    }

    public function getText(Location|Coordinate $location): ?string {
        // Coordinate?
        if ($location instanceof Coordinate) {
            $location = [$location];
        }

        // Select
        $selected = null;

        foreach ($location as $coordinate) {
            $number = $coordinate->line - $this->offset;

            if (isset($this->lines[$number])) {
                $selected[] = mb_substr($this->lines[$number], $coordinate->offset, $coordinate->length);
            } else {
                $selected = null;
                break;
            }
        }

        if ($selected === null) {
            return null;
        }

        // Return
        return implode("\n", $selected);
    }

    /**
     * @param iterable<array-key, array{Location, ?string}> $changes
     *
     * @return new<static>
     */
    public function mutate(iterable $changes): static {
        // Modify
        $lines   = $this->lines;
        $changes = $this->prepare($changes);
        $changes = $this->removeOverlaps($changes);
        $changes = $this->expand($changes);

        foreach ($changes as [$coordinate, $text]) {
            // Append?
            if ($coordinate->line === PHP_INT_MAX) {
                array_push($lines, ...$text);
                continue;
            }

            // Change
            $number  = $coordinate->line - $this->offset;
            $line    = $lines[$number] ?? '';
            $count   = count($text);
            $prefix  = mb_substr($line, 0, $coordinate->offset);
            $suffix  = $coordinate->length !== null
                ? mb_substr($line, $coordinate->offset + $coordinate->length)
                : '';
            $padding = mb_substr($line, 0, $coordinate->padding);

            if ($count > 1) {
                $insert = [];

                for ($t = 0; $t < $count; $t++) {
                    $insert[] = match (true) {
                        $t === 0          => rtrim($prefix.$text[$t]),
                        $t === $count - 1 => rtrim($padding.$text[$t].$suffix),
                        default           => rtrim($padding.$text[$t]),
                    };
                }

                array_splice($lines, $number, 1, $insert);
            } elseif ($count === 1) {
                $lines[$number] = rtrim($prefix.$text[0].$suffix);
            } elseif (($prefix !== '' && $prefix !== $padding) || $suffix !== '') {
                $lines[$number] = rtrim($prefix.$suffix);
            } else {
                unset($lines[$number]);
            }
        }

        // Return
        $editor        = clone $this;
        $editor->lines = array_values($lines);

        return $editor;
    }

    /**
     * @param iterable<array-key, array{Location, ?string}> $changes
     *
     * @return list<array{list<Coordinate>, ?string}>
     */
    protected function prepare(iterable $changes): array {
        $prepared = [];

        foreach ($changes as [$location, $text]) {
            $coordinates = [];

            foreach ($location as $coordinate) {
                $coordinates[] = $coordinate;
            }

            if ($coordinates !== []) {
                $prepared[] = [$coordinates, $text];
            }
        }

        return array_reverse($prepared);
    }

    /**
     * @param array<int, array{list<Coordinate>, ?string}> $changes
     *
     * @return list<array{Coordinate, list<string>}>
     */
    protected function expand(array $changes): array {
        $expanded = [];
        $append   = [];
        $sort     = static function (Coordinate $a, Coordinate $b): int {
            return $a->line <=> $b->line ?: $a->offset <=> $b->offset;
        };

        foreach ($changes as [$coordinates, $text]) {
            $text = match (true) {
                $text === null => [],
                $text === ''   => [''],
                default        => Text::getLines($text),
            };

            usort($coordinates, $sort);

            for ($i = 0, $c = count($coordinates); $i < $c; $i++) {
                $line = $i === $c - 1 ? array_slice($text, $i) : (array) ($text[$i] ?? null);

                if ($coordinates[$i]->line === PHP_INT_MAX) {
                    $append[] = [$coordinates[$i], $line];
                } else {
                    $expanded[] = [$coordinates[$i], $line];
                }
            }
        }

        usort($expanded, static fn ($a, $b) => -$sort($a[0], $b[0]));

        return array_merge($expanded, array_reverse($append));
    }

    /**
     * @param list<array{list<Coordinate>, ?string}> $changes
     *
     * @return array<int, array{list<Coordinate>, ?string}>
     */
    protected function removeOverlaps(array $changes): array {
        $used = [];

        foreach ($changes as $key => [$coordinates]) {
            $lines = [];

            foreach ($coordinates as $coordinate) {
                $lines[$coordinate->line][] = $coordinate;

                if ($this->isOverlapped($used, $coordinate)) {
                    $lines = [];
                    break;
                }
            }

            if ($lines !== []) {
                foreach ($lines as $line => $coords) {
                    $used[$line] = array_merge($used[$line] ?? [], $coords);
                }
            } else {
                unset($changes[$key]);
            }
        }

        // Return
        return $changes;
    }

    /**
     * @param array<int, array<int, Coordinate>> $coordinates
     */
    private function isOverlapped(array $coordinates, Coordinate $coordinate): bool {
        // Append?
        if ($coordinate->line === PHP_INT_MAX) {
            return false;
        }

        // Check
        $overlapped = false;

        foreach ($coordinates[$coordinate->line] ?? [] as $c) {
            $aStart     = $c->offset;
            $aEnd       = $aStart + ($c->length ?? PHP_INT_MAX) - 1;
            $bStart     = $coordinate->offset;
            $bEnd       = $bStart + ($coordinate->length ?? PHP_INT_MAX) - 1;
            $overlapped = !($bEnd < $aStart || $bStart > $aEnd);

            if ($overlapped) {
                break;
            }
        }

        return $overlapped;
    }
}
