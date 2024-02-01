<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\ScoutSupport;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition as ConditionType;
use Override;

class Condition extends BaseOperator {
    use HandlerOperator;
    use ScoutSupport;

    #[Override]
    public static function getName(): string {
        return 'Condition';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        return $provider->getType(ConditionType::class, $source, $context);
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Property condition.';
    }
}
