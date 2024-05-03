<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Spa\Package;
use Override;

/**
 * ISO 8601 DateTime.
 */
class DateTimeRule extends DateRule {
    public function __construct(
        protected readonly ConfigResolver $config,
        Translator $translator,
    ) {
        parent::__construct($translator);
    }

    #[Override]
    public function getValue(mixed $value): DateTimeInterface|null {
        $value = parent::getValue($value);
        $tz    = $this->config->getInstance()->get('app.timezone') ?: 'UTC';

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
