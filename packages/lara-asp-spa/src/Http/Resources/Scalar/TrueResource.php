<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

/**
 * @property true $resource
 */
class TrueResource extends BoolResource {
    public function __construct() {
        parent::__construct(true);
    }
}
