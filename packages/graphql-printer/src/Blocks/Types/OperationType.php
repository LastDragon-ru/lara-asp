<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\Core\Enum;
use Stringable;

/**
 * @internal
 */
class OperationType extends Enum implements Stringable {
    public static function query(): static {
        return self::make(__FUNCTION__);
    }

    public static function mutation(): static {
        return self::make(__FUNCTION__);
    }

    public static function subscription(): static {
        return self::make(__FUNCTION__);
    }
}
