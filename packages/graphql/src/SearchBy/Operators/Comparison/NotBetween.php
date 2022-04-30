<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComparisonOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorUnsupportedBuilder;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

use function implode;

class NotBetween extends Between implements ComparisonOperator {
    public static function getName(): string {
        return 'notBetween';
    }

    public function getFieldDescription(): string {
        return 'Outside a range.';
    }

    public function apply(
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        mixed $value,
    ): EloquentBuilder|QueryBuilder {
        return $builder->whereNotBetween($property, $value);
    }

    /**
     * @inheritDoc
     */
    public function call(Builder $search, object $builder, array $property, Argument $argument): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $property = implode('.', $property);
        $value    = Cast::toIterable($argument->toPlain());

        $builder->whereNotBetween($property, $value);

        return $builder;
    }
}
