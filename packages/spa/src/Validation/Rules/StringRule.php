<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Override;

use function is_string;

/**
 * String value.
 */
class StringRule extends Rule {
    #[Override]
    public function isValid(string $attribute, mixed $value): bool {
        return is_string($value);
    }
}
