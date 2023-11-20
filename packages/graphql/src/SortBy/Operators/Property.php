<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause;
use Override;

use function is_a;

class Property extends PropertyDirective implements Operator {
    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $provider->getType(Clause::class, $source);
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Property clause.';
    }

    #[Override]
    public function isBuilderSupported(string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, ScoutBuilder::class, true);
    }
}
