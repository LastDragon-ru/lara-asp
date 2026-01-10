<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Override;

use function is_finite;
use function is_float;

/**
 * Number value.
 */
class NumberRule extends IntRule {
    #[Override]
    public function isValid(string $attribute, mixed $value): bool {
        return (is_float($value) && is_finite($value)) || parent::isValid($attribute, $value);
    }
}
