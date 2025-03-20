<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor\Mutators;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;

use function array_key_first;
use function array_merge;
use function array_push;
use function array_reverse;
use function array_slice;
use function array_splice;
use function array_unshift;
use function array_values;
use function count;
use function mb_rtrim;
use function mb_substr;
use function usort;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

readonly class Mutator extends Base {
    /**
     * @param array<int, string>                                           $lines
     * @param iterable<mixed, array{iterable<mixed, Coordinate>, ?string}> $changes
     *
     * @return list<string>
     */
    public function __invoke(array $lines, iterable $changes): array {
        // Modify
        $changes   = $this->unpack($changes);
        $changes   = $this->cleanup($changes);
        $changes   = $this->prepare($changes);
        $startLine = (int) array_key_first($lines);

        if ($startLine !== 0) {
            $lines = array_values($lines);
        }

        foreach ($changes as [$coordinate, $text]) {
            // Prepend?
            if ($coordinate->line === PHP_INT_MIN) {
                array_unshift($lines, ...$text);
                continue;
            }

            // Append?
            if ($coordinate->line === PHP_INT_MAX) {
                array_push($lines, ...$text);
                continue;
            }

            // Change
            $number  = $coordinate->line - $startLine;
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
                        $t === 0          => mb_rtrim($prefix.$text[$t]),
                        $t === $count - 1 => mb_rtrim($padding.$text[$t].$suffix),
                        default           => mb_rtrim($padding.$text[$t]),
                    };
                }

                array_splice($lines, $number, 1, $insert);
            } elseif ($count === 1) {
                $lines[$number] = mb_rtrim($prefix.$text[0].$suffix);
            } elseif (($prefix !== '' && $prefix !== $padding) || $suffix !== '') {
                $lines[$number] = mb_rtrim($prefix.$suffix);
            } else {
                unset($lines[$number]);
            }
        }

        return array_values($lines);
    }

    /**
     * @param iterable<mixed, array{iterable<mixed, Coordinate>, ?string}> $changes
     *
     * @return list<array{list<Coordinate>, ?string}>
     */
    protected function unpack(iterable $changes): array {
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
    protected function prepare(array $changes): array {
        $expanded = [];
        $prepend  = [];
        $append   = [];

        foreach ($changes as [$coordinates, $text]) {
            $text = match (true) {
                $text === null => [],
                $text === ''   => [''],
                default        => Text::getLines($text),
            };

            usort($coordinates, $this->compare(...));

            for ($i = 0, $c = count($coordinates); $i < $c; $i++) {
                $line = $i === $c - 1 ? array_slice($text, $i) : (array) ($text[$i] ?? null);

                if ($coordinates[$i]->line === PHP_INT_MAX) {
                    $append[] = [$coordinates[$i], $line];
                } elseif ($coordinates[$i]->line === PHP_INT_MIN) {
                    $prepend[] = [$coordinates[$i], $line];
                } else {
                    $expanded[] = [$coordinates[$i], $line];
                }
            }
        }

        usort($expanded, fn ($a, $b) => -$this->compare($a[0], $b[0]));

        return array_merge($expanded, $prepend, array_reverse($append));
    }

    /**
     * @param list<array{list<Coordinate>, ?string}> $changes
     *
     * @return array<int, array{list<Coordinate>, ?string}>
     */
    protected function cleanup(array $changes): array {
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
     * @inheritDoc
     */
    #[Override]
    protected function isOverlapped(array $coordinates, Coordinate $coordinate, ?int &$key = null): bool {
        return $coordinate->line !== PHP_INT_MAX
            && $coordinate->line !== PHP_INT_MIN
            && parent::isOverlapped($coordinates, $coordinate, $key);
    }
}
