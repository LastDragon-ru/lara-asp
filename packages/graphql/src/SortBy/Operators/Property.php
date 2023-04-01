<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Traits\DirectiveName;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause;

use function is_a;

class Property extends PropertyDirective implements Operator {
    use DirectiveName;

    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $provider->getType(Clause::class, $source);
    }

    public function getFieldDescription(): string {
        return 'Property clause.';
    }

    public function isBuilderSupported(string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, ScoutBuilder::class, true);
    }
}
