<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Spa\Http\ValueProvider;
use LastDragon_ru\LaraASP\Spa\Provider;

/**
 * ISO 8601 Date.
 */
class DateRule implements Rule, ValueProvider {
    protected const Format = 'Y-m-d';

    protected Translator $translator;

    public function __construct(Translator $translator) {
        $this->translator = $translator;
    }

    // <editor-fold desc="Rule">
    // =========================================================================
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
     * @inheritdoc
     */
    public function message() {
        $package     = Provider::Package;
        $translation = $this->translator->get("{$package}::validation.date");

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
        return Date::createFromFormat(static::Format.'|', $value);
    }
    // </editor-fold>
}
