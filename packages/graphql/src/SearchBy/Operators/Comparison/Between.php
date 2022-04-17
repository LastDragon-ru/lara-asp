<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComparisonOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Range;

class Between extends BaseOperator implements ComparisonOperator {
    public static function getName(): string {
        return 'between';
    }

    protected function getDescription(): string {
        return 'Within a range.';
    }

    public function getDefinition(TypeProvider $provider, string $scalar, bool $nullable): string {
        return parent::getDefinition($provider, $provider->getType(Range::Name, $scalar), true);
    }

    public function apply(
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        mixed $value,
    ): EloquentBuilder|QueryBuilder {
        return $builder->whereBetween($property, $value);
    }
}
