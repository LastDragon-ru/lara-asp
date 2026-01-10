<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

/**
 * @property null $resource
 */
class NullResource extends ScalarResource {
    public function __construct() {
        parent::__construct(null);
    }
}
