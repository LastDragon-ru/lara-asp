<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package\DataProviders;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LastDragon_ru\LaraASP\GraphQL\Package\Data\Models\TestObject;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\UnknownValue;

/**
 * @internal
 */
class EloquentBuilderDataProvider extends ArrayDataProvider {
    public function __construct() {
        parent::__construct([
            'Builder' => [
                new UnknownValue(),
                static function (): EloquentBuilder {
                    return TestObject::query();
                },
            ],
        ]);
    }
}
