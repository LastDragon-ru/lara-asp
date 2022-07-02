<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;

/**
 * @internal Must not be used directly.
 */
class AllOf extends Logical {
    public static function getName(): string {
        return 'allOf';
    }

    public function getFieldDescription(): string {
        return 'All of the conditions must be true.';
    }

    public function getFieldType(TypeProvider $provider, string $type): ?string {
        return "[{$type}!]";
    }

    protected function getBoolean(): string {
        return 'and';
    }
}
