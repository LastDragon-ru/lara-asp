<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\BuilderUnknown;

/**
 * Can be used with a directive to define the builder type in case when auto-detection doesn't work.
 *
 * @see BuilderUnknown
 */
interface BuilderInfoProvider {
    /**
     * @return BuilderInfo|class-string<EloquentBuilder<Model>|QueryBuilder|ScoutBuilder|Collection<array-key, mixed>>
     */
    public function getBuilderInfo(): BuilderInfo|string;
}