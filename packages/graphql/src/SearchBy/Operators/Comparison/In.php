<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\WithScoutSupport;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

class In extends BaseOperator {
    use WithScoutSupport;

    public static function getName(): string {
        return 'in';
    }

    public function getFieldDescription(): string {
        return 'Within a set of values.';
    }

    public function getFieldType(TypeProvider $provider, string $type, ?bool $nullable): string {
        return "[{$type}!]";
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        $property = $property->getParent();
        $value    = (array) $argument->toPlain();

        if ($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder) {
            $builder->whereIn((string) $property, $value);
        } elseif ($builder instanceof ScoutBuilder) {
            $property = $this->getFieldResolver()->getField($builder->model, $property);

            $builder->whereIn($property, $value);
        } else {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        return $builder;
    }
}
