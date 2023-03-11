<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Flag;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

class Random extends BaseOperator {
    // <editor-fold desc="Directive">
    // =========================================================================
    public static function definition(): string {
        $name = static::getDirectiveName();

        return /** @lang GraphQL */ <<<GRAPHQL
            directive {$name} on FIELD_DEFINITION | INPUT_FIELD_DEFINITION
        GRAPHQL;
    }
    // </editor-fold>

    // <editor-fold desc="Operator">
    // =========================================================================
    public static function getName(): string {
        return 'random';
    }

    public function getFieldDescription(): string {
        return 'By random';
    }

    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $provider->getType(Flag::class, $source);
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $builder->inRandomOrder();

        return $builder;
    }
    // </editor-fold>
}
