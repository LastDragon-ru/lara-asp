<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;

/**
 * ISO 8601 DateTime.
 */
class DateTimeRule extends DateRule {
    protected const Format   = 'Y-m-d\TH:i:sP';
    protected const Timezone = 'UTC';

    protected Repository $config;

    public function __construct(Translator $translator, Repository $config) {
        parent::__construct($translator);

        $this->config = $config;
    }

    /**
     * @param mixed $value
     *
     * @return false|\Carbon\CarbonInterface
     */
    public function getValue($value) {
        $value = parent::getValue($value);
        $tz    = $this->config->get('app.timezone') ?: static::Timezone;

        if ($value && $tz) {
            $value = $value->setTimezone($tz);
        }

        return $value;
    }
}
