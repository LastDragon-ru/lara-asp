<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;

/**
 * @phpstan-type BuilderFactory \Closure(static):(\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>)
 */
class BuilderDataProvider extends MergeDataProvider {
    public function __construct() {
        parent::__construct([
            'Query'    => new QueryBuilderDataProvider(),
            'Eloquent' => new EloquentBuilderDataProvider(),
        ]);
    }
}
