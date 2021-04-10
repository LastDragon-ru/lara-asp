<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;

/**
 * @internal Must not be used directly.
 */
class IsNotNull extends BaseOperator implements ComparisonOperator {
    public function getName(): string {
        return 'isNotNull';
    }

    protected function getDescription(): string {
        return 'Is NOT NULL?';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, $map[Directive::TypeFlag], true);
    }

    public function apply(
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        mixed $value,
    ): EloquentBuilder|QueryBuilder {
        return $builder->whereNotNull($property);
    }
}
