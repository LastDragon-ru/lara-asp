<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Override;

use function is_finite;
use function is_float;

/**
 * Number value.
 */
class NumberRule extends IntRule {
    /**
     * @inheritDoc
     */
    #[Override]
    public function passes($attribute, $value) {
        return (is_float($value) && is_finite($value)) || parent::passes($attribute, $value);
    }
}
