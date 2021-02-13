<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use DateTimeInterface;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Spa\Http\ValueProvider;
use LastDragon_ru\LaraASP\Spa\Package;

/**
 * ISO 8601 Date.
 */
class DateRule extends Rule implements ValueProvider {
    /**
     * @inheritdoc
     */
    public function passes($attribute, $value) {
        $passes = false;

        try {
            $date   = $this->getValue($value);
            $passes = $date && $date->format($this->getFormat()) === $value;
        } catch (InvalidArgumentException $exception) {
            // ignored
        }

        return $passes;
    }

    public function getValue(mixed $value): DateTimeInterface|null {
        return Date::createFromFormat("{$this->getFormat()}|", $value) ?: null;
    }

    protected function getFormat(): string {
        return Package::DateFormat;
    }
}
