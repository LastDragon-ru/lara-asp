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

class Property extends PropertyDirective implements Operator {
    use DirectiveName;

    public function getFieldType(TypeProvider $provider, TypeSource $type): string {
        return $provider->getType(Clause::class, $type);
    }

    public function getFieldDescription(): string {
        return 'Property clause.';
    }

    public function isBuilderSupported(object $builder): bool {
        return $builder instanceof EloquentBuilder
            || $builder instanceof ScoutBuilder;
    }
}
