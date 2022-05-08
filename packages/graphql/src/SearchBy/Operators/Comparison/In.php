<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

class In extends BaseOperator {
    public static function getName(): string {
        return 'in';
    }

    public function getFieldDescription(): string {
        return 'Within a set of values.';
    }

    public function getFieldType(TypeProvider $provider, string $type): ?string {
        return "[{$type}!]";
    }

    public function call(Builder $search, object $builder, Property $property, Argument $argument): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $property = (string) $property;
        $value    = $argument->toPlain();

        $builder->whereIn($property, $value);

        return $builder;
    }
}
