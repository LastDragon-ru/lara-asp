<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package\DataProviders;

use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Package\Data\Models\TestObject;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\UnknownValue;

/**
 * @internal
 */
class QueryBuilderDataProvider extends ArrayDataProvider {
    public function __construct() {
        parent::__construct([
            'Builder' => [
                new UnknownValue(),
                static function (): QueryBuilder {
                    return TestObject::query()->toBase();
                },
            ],
        ]);
    }
}
