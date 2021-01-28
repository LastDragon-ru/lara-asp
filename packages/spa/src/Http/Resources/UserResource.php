<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Foundation\Auth\User;

/**
 * @property \Illuminate\Foundation\Auth\User $resource
 */
class UserResource extends Resource {
    public function __construct(User $resource) {
        parent::__construct($resource);
    }

    public function toArray($request) {
        return [
            'name'     => $this->resource->name,
            'verified' => $this->resource->hasVerifiedEmail(),
        ];
    }
}
