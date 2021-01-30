<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use function is_finite;
use function is_float;

/**
 * Number value.
 */
class NumberRule extends IntRule {
    /**
     * @inheritdoc
     */
    public function passes($attribute, $value) {
        return (is_float($value) && is_finite($value)) || parent::passes($attribute, $value);
    }
}
