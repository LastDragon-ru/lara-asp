<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use Stringable;

use function assert;
use function is_bool;
use function is_float;
use function is_int;
use function is_iterable;
use function is_scalar;
use function is_string;

class Cast {
    public static function toInt(mixed $value): int {
        assert(is_int($value));

        return $value;
    }

    public static function toFloat(mixed $value): float {
        assert(is_float($value));

        return $value;
    }

    public static function toString(mixed $value): string {
        assert(is_string($value));

        return $value;
    }

    public static function toScalar(mixed $value): int|float|string|bool {
        assert(is_scalar($value));

        return $value;
    }

    public static function toNumber(mixed $value): int|float {
        assert(is_int($value) || is_float($value));

        return $value;
    }

    public static function toBool(mixed $value): bool {
        assert(is_bool($value));

        return $value;
    }

    public static function toStringable(mixed $value): Stringable|string {
        assert(is_string($value) || $value instanceof Stringable);

        return $value;
    }

    /**
     * @return iterable<array-key, mixed>
     */
    public static function toIterable(mixed $value): iterable {
        assert(is_iterable($value));

        return $value;
    }
}
