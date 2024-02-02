<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Operator;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

class In extends Operator {
    use WithScoutSupport;

    #[Override]
    public static function getName(): string {
        return 'in';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Within a set of values.';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        return "[{$source->getTypeName()}!]";
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
        $value    = (array) $argument->toPlain();

        if ($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder) {
            $builder->whereIn($property, $value);
        } elseif ($builder instanceof ScoutBuilder) {
            $builder->whereIn($property, $value);
        } else {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        return $builder;
    }
}
