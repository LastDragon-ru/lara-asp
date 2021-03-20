<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @internal Must not be used directly.
 */
class AllOf extends Logical {
    public function getName(): string {
        return 'allOf';
    }

    protected function getDescription(): string {
        return 'All of the conditions must be true.';
    }

    public function apply(EloquentBuilder|QueryBuilder $builder, Closure $nested): EloquentBuilder|QueryBuilder {
        return $builder->where($nested, null, null, 'and');
    }
}
