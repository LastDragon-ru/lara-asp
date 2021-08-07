<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Foundation\Auth\User;

/**
 * @property User $resource
 */
class UserResource extends Resource {
    public function __construct(User $resource) {
        parent::__construct($resource);
    }

    /**
     * @inheritdoc
     */
    public function toArray(mixed $request): array {
        return [
            'name'     => $this->resource->name,
            'verified' => $this->resource->hasVerifiedEmail(),
        ];
    }
}
