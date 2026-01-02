<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Defaults\Output as Implementation;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;

/**
 * @property-read positive-int $width the number of characters per line (columns)
 *
 * @phpstan-require-extends Implementation
 */
interface Output {
    public function write(string $line, Verbosity $verbosity): void;

    /**
     * String length in visible characters without styles/formatting/etc.
     *
     * @return int<0, max>
     */
    public function length(string $string): int;

    /**
     * @param positive-int $limit
     *
     * @return list<non-empty-string>
     */
    public function split(string $string, int $limit): array;
}
