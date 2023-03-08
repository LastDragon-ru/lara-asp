<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator as Marker;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\DirectiveName;

abstract class BaseOperator extends OperatorDirective implements Operator, Marker {
    use DirectiveName;

    public function getFieldType(TypeProvider $provider, TypeSource $type): string {
        return $type->getTypeName();
    }

    public function isBuilderSupported(object $builder): bool {
        return $builder instanceof EloquentBuilder
            || $builder instanceof QueryBuilder;
    }
}
