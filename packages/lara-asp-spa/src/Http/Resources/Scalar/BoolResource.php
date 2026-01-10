<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

/**
 * @property bool $resource
 */
class BoolResource extends ScalarResource {
    public function __construct(bool $resource) {
        parent::__construct($resource);
    }
}
