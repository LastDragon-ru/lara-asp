<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Utils;

use DateInterval;

/**
 * @internal
 */
class DurationFormatter {
    final protected const SecondsInMinute = 60;
    final protected const SecondsInHour   = 60 * self::SecondsInMinute;
    final protected const SecondsInDay    = 24 * self::SecondsInHour;
    final protected const SecondsInMonth  = 30 * self::SecondsInDay;
    final protected const SecondsInYear   = 365 * self::SecondsInDay;

    public static function getTimestamp(DateInterval $interval): float {
        return ($interval->invert ? -1 : 1) * (0
                + $interval->y * self::SecondsInYear
                + $interval->m * self::SecondsInMonth
                + $interval->d * self::SecondsInDay
                + $interval->h * self::SecondsInHour
                + $interval->i * self::SecondsInMinute
                + $interval->s
                + $interval->f);
    }
}
