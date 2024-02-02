<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\ScoutSupport;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

class Equal extends Operator {
    use ScoutSupport;

    #[Override]
    public static function getName(): string {
        return 'equal';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Equal (`=`).';
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Property $property,
        Argument $argument,
        Context $context,
    ): object {
        $property = $this->resolver->getProperty($builder, $property->getParent());
        $value    = $argument->toPlain();

        if ($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder) {
            $builder->where($property, '=', $value);
        } elseif ($builder instanceof ScoutBuilder) {
            $builder->where($property, $value);
        } else {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        return $builder;
    }
}
