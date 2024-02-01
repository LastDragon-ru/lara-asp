<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause as ClauseType;
use Override;

class Clause extends BaseOperator {
    use HandlerOperator;

    #[Override]
    public static function getName(): string {
        return 'condition';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        return '['.$provider->getType(ClauseType::class, $source, $context).'!]';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Field clause.';
    }
}
