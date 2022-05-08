<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorInvalidArguments;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use function array_filter;
use function count;
use function is_array;

abstract class Logical extends BaseOperator {
    public function call(Builder $search, object $builder, Property $property, Argument $argument): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        // The last item is the name of the operator not a property
        $property   = $property->getParent();
        $conditions = $this->getConditions($argument);

        foreach ($conditions as $arguments) {
            $builder->where(
                static function (EloquentBuilder|QueryBuilder $builder) use (
                    $search,
                    $arguments,
                    $property
                ): object {
                    return $search->where($builder, $arguments, $property);
                },
                null,
                null,
                $this->getBoolean(),
            );
        }

        return $builder;
    }

    abstract protected function getBoolean(): string;

    /**
     * @return array<ArgumentSet>
     */
    protected function getConditions(Argument $argument): array {
        // ArgumentSet?
        $value = $argument->value;

        if ($argument->value instanceof ArgumentSet) {
            return [$argument->value];
        }

        // Array?
        $expected = 'array<'.ArgumentSet::class.'>';

        if (!is_array($value)) {
            throw new OperatorInvalidArguments($this, $expected, $value);
        }

        $count = count($value);
        $args  = array_filter($value, static function (mixed $value): bool {
            return $value instanceof ArgumentSet;
        });

        if ($count !== count($args)) {
            throw new OperatorInvalidArguments($this, $expected, $value);
        }

        return $args;
    }
}
