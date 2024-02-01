<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use Override;

class Property extends BaseOperator {
    use HandlerOperator;

    #[Override]
    public static function getName(): string {
        return 'property';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Property condition.';
    }

    #[Override]
    public function isAvailable(string $builder, Context $context): bool {
        return true;
    }
}
