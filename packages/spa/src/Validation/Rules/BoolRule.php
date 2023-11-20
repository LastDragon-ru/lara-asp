<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Override;

use function is_bool;

/**
 * Boolean value.
 */
class BoolRule extends Rule {
    /**
     * @inheritDoc
     */
    #[Override]
    public function passes($attribute, $value) {
        return is_bool($value);
    }
}
