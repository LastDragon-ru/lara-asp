<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use Override;

/**
 * @internal Must not be used directly.
 */
class AnyOf extends Logical {
    #[Override]
    public static function getName(): string {
        return 'anyOf';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Any of the conditions must be true.';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        $parent = parent::getFieldType($provider, $source, $context);
        $type   = "[{$parent}!]";

        return $type;
    }

    #[Override]
    protected function getBoolean(): string {
        return 'or';
    }
}
