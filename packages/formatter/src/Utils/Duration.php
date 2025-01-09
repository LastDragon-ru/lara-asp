<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Utils;

use DateInterval;

/**
 * @internal
 */
class Duration {
    final public const int SecondsInMinute = 60;
    final public const int SecondsInHour   = 60 * self::SecondsInMinute;
    final public const int SecondsInDay    = 24 * self::SecondsInHour;
    final public const int SecondsInMonth  = 30 * self::SecondsInDay;
    final public const int SecondsInYear   = 365 * self::SecondsInDay;

    public static function getTimestamp(DateInterval|float|int|null $interval): float {
        return match (true) {
            $interval instanceof DateInterval => ($interval->invert !== 0 ? -1 : 1) * (0
                    + $interval->y * self::SecondsInYear
                    + $interval->m * self::SecondsInMonth
                    + $interval->d * self::SecondsInDay
                    + $interval->h * self::SecondsInHour
                    + $interval->i * self::SecondsInMinute
                    + $interval->s
                    + $interval->f),
            $interval === null                => 0,
            default                           => $interval,
        };
    }
}
