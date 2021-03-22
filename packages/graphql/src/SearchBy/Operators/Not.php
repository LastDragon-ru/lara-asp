<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\LogicalOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directive;

/**
 * @internal Must not be used directly.
 * @see      \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorNegationable
 */
class Not extends BaseOperator implements LogicalOperator {
    public const Name = 'not';

    public function getName(): string {
        return static::Name;
    }

    protected function getDescription(): string {
        return 'Not.';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, $map[Directive::TypeFlag], true);
    }

    public function apply(EloquentBuilder|QueryBuilder $builder, Closure $nested): EloquentBuilder|QueryBuilder {
        return $builder->where($nested, null, null, 'and not');
    }
}
