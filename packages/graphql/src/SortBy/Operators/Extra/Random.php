<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Flag;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

class Random extends BaseOperator {
    public static function getName(): string {
        return 'random';
    }

    public function getFieldDescription(): string {
        return 'By random';
    }

    public function getFieldType(TypeProvider $provider, string $type, ?bool $nullable): string {
        return $provider->getType(Flag::class, null, null);
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $builder->inRandomOrder();

        return $builder;
    }
}
