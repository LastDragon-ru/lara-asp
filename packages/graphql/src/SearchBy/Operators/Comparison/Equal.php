<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\WithScoutSupport;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

class Equal extends BaseOperator {
    use WithScoutSupport;

    public static function getName(): string {
        return 'equal';
    }

    public function getFieldDescription(): string {
        return 'Equal (`=`).';
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        $property = $property->getParent();
        $value    = $argument->toPlain();

        if ($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder) {
            $builder->where((string) $property, '=', $value);
        } elseif ($builder instanceof ScoutBuilder) {
            $property = $this->getFieldResolver()->getField($builder->model, $property);

            $builder->where($property, $value);
        } else {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        return $builder;
    }
}
