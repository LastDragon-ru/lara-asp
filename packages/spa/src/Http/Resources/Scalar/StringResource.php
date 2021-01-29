<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

/**
 * @property string $resource
 */
class StringResource extends ScalarResource {
    public function __construct(string $resource) {
        parent::__construct($resource);
    }
}
