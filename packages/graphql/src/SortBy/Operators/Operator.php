<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\TypeExtender;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Operator as Marker;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Support\Contracts\TypeExtensionManipulator;
use Override;

use function is_a;

abstract class Operator extends OperatorDirective implements Marker, TypeExtensionManipulator {
    use TypeExtender;

    #[Override]
    protected function isBuilderSupported(string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, QueryBuilder::class, true);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getExtendableScalars(AstManipulator $manipulator): array {
        return Operators::getExtendableScalars($manipulator);
    }
}
