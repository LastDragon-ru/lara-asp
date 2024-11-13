<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber;

use IntlException;
use NumberFormatter;
use Override;

/**
 * @see NumberFormatter
 * @extends IntlFormat<IntlOptions, float|int|null>
 */
class IntlNumberFormat extends IntlFormat {
    #[Override]
    public function __invoke(mixed $value): string {
        $formatted = $this->formatter->format($value ?? 0);

        if ($formatted === false) {
            throw new IntlException($this->formatter->getErrorMessage(), $this->formatter->getErrorCode());
        }

        return $formatted;
    }
}
