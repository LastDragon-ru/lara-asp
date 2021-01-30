<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Spa\Http\ValueProvider;

/**
 * ISO 8601 Date.
 */
class DateRule extends Rule implements ValueProvider {
    protected const Format = 'Y-m-d';

    /**
     * @inheritdoc
     */
    public function passes($attribute, $value) {
        $passes = false;

        try {
            $date   = $this->getValue($value);
            $passes = $date && $date->format(static::Format) === $value;
        } catch (InvalidFormatException $exception) {
            // ignored
        }

        return $passes;
    }

    /**
     * @param mixed $value
     *
     * @return false|\Carbon\CarbonInterface
     */
    public function getValue($value) {
        return Date::createFromFormat(static::Format.'|', $value);
    }
}
