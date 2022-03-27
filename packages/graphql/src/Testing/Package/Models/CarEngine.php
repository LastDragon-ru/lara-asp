<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;

/**
 * @internal
 *
 * @property string $id
 * @property string $car_id
 * @property int    $installed
 */
class CarEngine extends Model {
    public const Id = '8754db24-28ce-4b14-9c7e-657ea91c0593';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct('car_engines', self::Id, $attributes);
    }
}
