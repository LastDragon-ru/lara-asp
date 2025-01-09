<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;

/**
 * @internal
 *
 * @property string $id
 * @property string $name
 */
class Organization extends Model {
    public const string Id = '9c450f2a-8600-4e1e-8b76-3743f0f0e642';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct('organizations', self::Id, $attributes);
    }
}
