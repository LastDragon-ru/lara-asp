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
    /**
     * @inheritDoc
     */
    #[Override]
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

    #[Override]
    public function getValue(mixed $value): DateTimeInterface|null {
        return Date::createFromFormat("{$this->getFormat()}|", $value) ?: null;
    }

    protected function getFormat(): string {
        return Package::DateFormat;
    }
}
