<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Contracts;

use LastDragon_ru\LaraASP\Formatter\Formatter;

/**
 * The instance will be created through container with the following additional
 * arguments:
 *
 * * `$formatter`: {@see Formatter} - the current formatter instance (can be used to get locale/timezone).
 * * `$options` (array) - formatter options defined inside app config (may contain `null`s).
 *
 * @see Formatter
 *
 * @template TOptions of object|null
 * @template TValue
 */
interface Format {
    /**
     * @param TValue $value
     */
    public function __invoke(mixed $value): string;
}
