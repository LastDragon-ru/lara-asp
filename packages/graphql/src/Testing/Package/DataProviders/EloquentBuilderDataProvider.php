<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Model;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
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
                static function (TestCase $test): EloquentBuilder {
                    return Model::query();
                },
            ],
        ]);
    }
}
