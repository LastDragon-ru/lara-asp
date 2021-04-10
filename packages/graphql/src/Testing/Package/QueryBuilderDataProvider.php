<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\Unknown;

class QueryBuilderDataProvider extends ArrayDataProvider {
    public function __construct() {
        parent::__construct([
            'Builder' => [
                new Unknown(),
                static function (TestCase $test): QueryBuilder {
                    return $test->getApplication()->make('db')->table('tmp');
                },
            ],
        ]);
    }
}
