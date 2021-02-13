<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

use Illuminate\Http\JsonResponse;
use LastDragon_ru\LaraASP\Spa\Http\Resources\Resource;

/**
 * @property int|float|string|bool|null $resource
 */
abstract class ScalarResource extends Resource {
    protected function __construct(int|float|string|bool|null $resource) {
        parent::__construct($resource);
    }

    public function toResponse($request) {
        return (new JsonResponse())->setData($this->resource);
    }
}
