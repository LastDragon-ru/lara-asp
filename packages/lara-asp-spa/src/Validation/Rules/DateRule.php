<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use DateTimeInterface;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Http\ValueProvider;
use LastDragon_ru\LaraASP\Spa\Package;
use Override;

/**
 * ISO 8601 Date.
 */
class DateRule extends Rule implements ValueProvider {
    #[Override]
    public function isValid(string $attribute, mixed $value): bool {
        $valid = false;

        try {
            $date  = $this->getValue($value);
            $valid = $date !== null && $date->format($this->getFormat()) === $value;
        } catch (InvalidArgumentException $exception) {
            // ignored
        }

        return $valid;
    }

    #[Override]
    public function getValue(mixed $value): ?DateTimeInterface {
        $value = Date::createFromFormat("{$this->getFormat()}|", $value);
        $value = $value instanceof DateTimeInterface ? $value : null;

        return $value;
    }

    protected function getFormat(): string {
        return Package::DateFormat;
    }
}
