<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\Spa\Package;
use Override;

/**
 * ISO 8601 DateTime.
 */
class DateTimeRule extends DateRule {
    #[Override]
    public function getValue(mixed $value): DateTimeInterface|null {
        $repository = Container::getInstance()->make(Repository::class);
        $value      = parent::getValue($value);
        $tz         = $repository->get('app.timezone') ?: 'UTC';

        if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
            $value = $value->setTimezone($tz);
        }

        return $value;
    }

    #[Override]
    protected function getFormat(): string {
        return Package::DateTimeFormat;
    }
}
