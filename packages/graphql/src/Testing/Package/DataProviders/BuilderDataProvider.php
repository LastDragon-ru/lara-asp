<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;

/**
 * @phpstan-type BuilderFactory Closure(static):(QueryBuilder|EloquentBuilder<EloquentModel>)
 *
 * @internal
 */
class BuilderDataProvider extends MergeDataProvider {
    public function __construct() {
        parent::__construct([
            'Query'    => new QueryBuilderDataProvider(),
            'Eloquent' => new EloquentBuilderDataProvider(),
        ]);
    }
}
