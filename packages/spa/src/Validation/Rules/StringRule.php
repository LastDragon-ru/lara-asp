<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use function is_string;

/**
 * String value.
 */
class StringRule extends Rule {
    /**
     * @inheritDoc
     */
    public function passes($attribute, $value) {
        return is_string($value);
    }
}
