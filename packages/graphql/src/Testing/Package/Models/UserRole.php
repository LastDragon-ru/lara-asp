<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Pivot;

/**
 * @internal
 *
 * @property string $id
 * @property string $user_id
 * @property string $role_id
 */
class UserRole extends Pivot {
    public const string Id = '42180990-a4ef-48f0-9c2c-3158595f9da7';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct('user_roles', self::Id, $attributes);
    }
}
