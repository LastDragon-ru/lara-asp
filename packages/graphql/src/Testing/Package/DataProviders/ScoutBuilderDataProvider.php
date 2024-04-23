<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\TestObjectSearchable;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\UnknownValue;

/**
 * @internal
 */
class ScoutBuilderDataProvider extends ArrayDataProvider {
    public function __construct() {
        parent::__construct([
            'Builder' => [
                new UnknownValue(),
                static function (): ScoutBuilder {
                    return TestObjectSearchable::search();
                },
            ],
        ]);
    }
}
