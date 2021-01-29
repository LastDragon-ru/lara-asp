<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

/**
 * @property float $resource
 */
class FloatResource extends ScalarResource {
    public function __construct(float $resource) {
        parent::__construct($resource);
    }
}
