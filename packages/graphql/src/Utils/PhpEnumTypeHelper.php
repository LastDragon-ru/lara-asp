<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use GraphQL\Type\Definition\PhpEnumType;
use UnitEnum;

/**
 * @internal
 */
class PhpEnumTypeHelper extends PhpEnumType {
    /**
     * @return class-string<UnitEnum>
     */
    public static function getEnumClass(PhpEnumType $type): string {
        return $type->enumClass;
    }
}
