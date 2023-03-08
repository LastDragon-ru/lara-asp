<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;

/**
 * @internal Must not be used directly.
 */
class AnyOf extends Logical {
    public static function getName(): string {
        return 'anyOf';
    }

    public function getFieldDescription(): string {
        return 'Any of the conditions must be true.';
    }

    public function getFieldType(TypeProvider $provider, TypeSource $type): string {
        return "[{$type->getTypeName()}!]";
    }

    protected function getBoolean(): string {
        return 'or';
    }
}
