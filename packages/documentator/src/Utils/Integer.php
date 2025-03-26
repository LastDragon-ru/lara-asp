<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use function is_int;

use const PHP_INT_MAX;

/**
 * @internal
 */
class Integer {
    public static function add(int $a, int $b): int {
        $sum = $a + $b;
        $sum = is_int($sum) ? $sum : PHP_INT_MAX; // @phpstan-ignore function.alreadyNarrowedType (if overflow it will be float)

        return $sum;
    }
}
