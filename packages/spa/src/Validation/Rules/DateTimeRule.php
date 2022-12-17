<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use LastDragon_ru\LaraASP\Spa\Package;

use function config;

/**
 * ISO 8601 DateTime.
 */
class DateTimeRule extends DateRule {
    public function getValue(mixed $value): DateTimeInterface|null {
        $value = parent::getValue($value);
        $tz    = config('app.timezone') ?: 'UTC';

        if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
            $value = $value->setTimezone($tz);
        }

        return $value;
    }

    protected function getFormat(): string {
        return Package::DateTimeFormat;
    }
}
