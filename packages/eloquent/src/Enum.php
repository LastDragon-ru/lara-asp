<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use LastDragon_ru\LaraASP\Core\Enum as BaseEnum;
use LastDragon_ru\LaraASP\Eloquent\Casts\EnumCast;

/**
 * @deprecated Please use native PHP enums.
 */
abstract class Enum extends BaseEnum implements Castable {
    /**
     * @param array<mixed> $arguments
     *
     * @return EnumCast
     */
    public static function castUsing(array $arguments): CastsAttributes {
        return new EnumCast(static::class);
    }
}
