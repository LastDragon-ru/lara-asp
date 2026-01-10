<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

/**
 * @property int $resource
 */
class IntResource extends ScalarResource {
    public function __construct(int $resource) {
        parent::__construct($resource);
    }
}
