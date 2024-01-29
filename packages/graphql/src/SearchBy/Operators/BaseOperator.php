<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator as Marker;
use Override;

use function is_a;

abstract class BaseOperator extends OperatorDirective implements Operator, Marker {
    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        return $source->getTypeName();
    }

    #[Override]
    public function isAvailable(string $builder, Context $context): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, QueryBuilder::class, true);
    }
}
