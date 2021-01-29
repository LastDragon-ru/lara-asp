<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

/**
 * @property false $resource
 */
class FalseResource extends BoolResource {
    public function __construct() {
        parent::__construct(false);
    }
}
