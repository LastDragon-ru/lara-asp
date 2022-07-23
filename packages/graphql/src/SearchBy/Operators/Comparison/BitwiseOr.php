<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

class BitwiseOr extends BaseOperator {
    public static function getName(): string {
        return 'bitwiseOr';
    }

    public function getFieldDescription(): string {
        return 'Bitwise OR (`|`).';
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $property = (string) $property->getParent();
        $value    = $argument->toPlain();
        $builder  = $builder->where($property, '|', $value);

        return $builder;
    }
}
