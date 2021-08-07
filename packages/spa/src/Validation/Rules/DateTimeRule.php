<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Spa\Package;

/**
 * ISO 8601 DateTime.
 */
class DateTimeRule extends DateRule {
    protected Repository $config;

    public function __construct(Translator $translator, Repository $config) {
        parent::__construct($translator);

        $this->config = $config;
    }

    public function getValue(mixed $value): DateTimeInterface|null {
        $value = parent::getValue($value);
        $tz    = $this->config->get('app.timezone') ?: 'UTC';

        if ($tz && ($value instanceof DateTime || $value instanceof DateTimeImmutable)) {
            $value = $value->setTimezone($tz);
        }

        return $value;
    }

    protected function getFormat(): string {
        return Package::DateTimeFormat;
    }
}
