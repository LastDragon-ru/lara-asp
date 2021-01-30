<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Spa\Provider;

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

    // <editor-fold desc="Rule">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function message() {
        $package     = Provider::Package;
        $translation = $this->translator->get("{$package}::validation.datetime");

        return $translation;
    }
    // </editor-fold>

    // <editor-fold desc="ValueProvider">
    // =========================================================================
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
    // </editor-fold>
}
