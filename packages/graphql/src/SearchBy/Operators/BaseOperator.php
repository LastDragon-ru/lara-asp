<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator as Marker;

use function is_a;

abstract class BaseOperator extends OperatorDirective implements Operator, Marker {
    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $source->getTypeName();
    }

    public function isBuilderSupported(string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, QueryBuilder::class, true);
    }
}
