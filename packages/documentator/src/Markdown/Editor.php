<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;
use Stringable;

use function array_key_last;
use function array_merge;
use function array_reverse;
use function count;
use function implode;
use function iterator_to_array;
use function mb_substr;
use function trim;
use function usort;

use const PHP_INT_MAX;

/**
 * @internal
 */
class Editor implements Stringable {
    public function __construct(
        /**
         * @var array<int, string>
         */
        private array $lines,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return implode("\n", $this->lines);
    }

    /**
     * @return array<int, string>
     */
    public function getLines(): array {
        return $this->lines;
    }

    public function getText(Location|Coordinate $location): ?string {
        // Coordinate?
        if ($location instanceof Coordinate) {
            $location = [$location];
        }

        // Select
        $selected = null;

        foreach ($location as $coordinate) {
            if (isset($this->lines[$coordinate->line])) {
                $selected[] = mb_substr($this->lines[$coordinate->line], $coordinate->offset, $coordinate->length);
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
     * @param array<array-key, array{Location, ?string}> $changes
     *
     * @return new<static>
     */
    public function mutate(array $changes): static {
        // Modify
        $lines    = $this->lines;
        $changes  = $this->removeOverlaps($changes);
        $changes  = $this->expand($changes);
        $paddings = [];

        foreach ($changes as $change) {
            [$coordinate, $padding, $text] = $change;
            $line                          = $lines[$coordinate->line] ?? '';
            $prefix                        = mb_substr($line, 0, $coordinate->offset);
            $suffix                        = $coordinate->length
                ? mb_substr($line, $coordinate->offset + $coordinate->length)
                : '';
            $lines[$coordinate->line]      = $prefix.$text.$suffix;
            $paddings[$coordinate->line]   = $padding;

            if ($text === null && !$suffix) {
                $lines[$coordinate->line] = trim($prefix);
            }
        }

        // Markdown Parser uses the empty line right after the block as an
        // End Line. We are attempting to preserve them, and also merge
        // multiple empty lines into one.
        $previous = '';

        foreach ($lines as $line => $text) {
            $content = mb_substr($text, $paddings[$line] ?? 0);
            $padding = mb_substr($text, 0, $paddings[$line] ?? 0);

            if ($content === '') {
                if ($previous !== '') {
                    $lines[$line] = $padding;
                } else {
                    unset($lines[$line]);
                }
            }

            $previous = $content;
        }

        // Remove last line if empty
        $last    = array_key_last($lines);
        $content = mb_substr($lines[$last] ?? '', $paddings[$last] ?? 0);

        if ($content === '') {
            unset($lines[$last]);
        }

        // Return
        $editor        = clone $this;
        $editor->lines = $lines;

        return $editor;
    }

    /**
     * @param array<array-key, array{Location, ?string}> $changes
     *
     * @return list<array{Coordinate, int, ?string}>
     */
    protected function expand(array $changes): array {
        $expanded = [];
        $sort     = static function (Coordinate $a, Coordinate $b): int {
            return $a->line <=> $b->line ?: $a->offset <=> $b->offset;
        };

        foreach (array_reverse($changes, true) as $change) {
            [$location, $text] = $change;
            $coordinates       = iterator_to_array($location);
            $text              = $text ? Text::getLines($text) : [];
            $line              = 0;

            usort($coordinates, $sort);

            foreach ($coordinates as $coordinate) {
                $expanded[] = [$coordinate, $location->getPadding(), $text[$line++] ?? null];
            }

            // If `$text` contains more lines than `$coordinates` that means
            // that these lines should be added after the last `$coordinate`.
            //
            // Not supported yet 🤷‍♂️
        }

        usort($expanded, static fn ($a, $b) => -$sort($a[0], $b[0]));

        return $expanded;
    }

    /**
     * @param array<array-key, array{Location, ?string}> $changes
     *
     * @return array<array-key, array{Location, ?string}>
     */
    protected function removeOverlaps(array $changes): array {
        $used = [];

        foreach (array_reverse($changes, true) as $key => $change) {
            [$location]  = $change;
            $coordinates = iterator_to_array($location);

            usort($coordinates, static function (Coordinate $a, Coordinate $b): int {
                return $b->line <=> $a->line;
            });

            foreach ($coordinates as $coordinate) {
                if ($this->isOverlapped($used, $coordinate)) {
                    $coordinates = null;
                    break;
                }
            }

            if ($coordinates) {
                $used = array_merge($used, $coordinates);
            } else {
                unset($changes[$key]);
            }
        }

        // Return
        return $changes;
    }

    /**
     * @param array<int, Coordinate> $coordinates
     */
    private function isOverlapped(array $coordinates, Coordinate $coordinate): bool {
        $overlapped = false;

        for ($i = count($coordinates) - 1; $i >= 0; $i--) {
            if ($coordinate->line === $coordinates[$i]->line) {
                $aStart     = $coordinates[$i]->offset;
                $aEnd       = $aStart + ($coordinates[$i]->length ?? PHP_INT_MAX);
                $bStart     = $coordinate->offset;
                $bEnd       = $bStart + ($coordinate->length ?? PHP_INT_MAX);
                $overlapped = !($bEnd < $aStart || $bStart > $aEnd);
            }

            if ($overlapped || $coordinate->line < $coordinates[$i]->line) {
                break;
            }
        }

        return $overlapped;
    }
}
