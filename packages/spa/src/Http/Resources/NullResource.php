<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Http\JsonResponse;

/**
 * @property \Illuminate\Foundation\Auth\User $resource
 */
class NullResource extends Resource {
    public function __construct() {
        parent::__construct(null);
    }

    public function toResponse($request) {
        return (new JsonResponse())->setData(null);
    }
}
