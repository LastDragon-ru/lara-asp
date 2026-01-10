<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber;

use IntlException;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageConfig;
use NumberFormatter;
use Override;

use function is_array;

/**
 * @see NumberFormatter
 * @extends IntlFormat<?IntlCurrencyOptions, array{float|int|null, ?non-empty-string}|float|int|null>
 */
readonly class IntlCurrencyFormat extends IntlFormat {
    /**
     * @var non-empty-string|null
     */
    protected ?string $currency;

    /**
     * @param list<IntlCurrencyOptions|null> $options
     */
    public function __construct(PackageConfig $config, Formatter $formatter, array $options = []) {
        // Parent
        parent::__construct($config, $formatter, [
            new IntlCurrencyOptions(NumberFormatter::CURRENCY),
            ...$options,
        ]);

        // Currency
        $currency = null;

        foreach ($options as $option) {
            if ($option === null) {
                continue;
            }

            $currency ??= $option->currency;
        }

        $this->currency = $currency;
    }

    #[Override]
    public function __invoke(mixed $value): string {
        [$value, $currency] = is_array($value) ? $value : [$value, null];
        $value            ??= 0;
        $currency         ??= $this->currency ?? $this->formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE);
        $formatted          = $this->formatter->formatCurrency($value, $currency);

        if ($formatted === false) {
            throw new IntlException($this->formatter->getErrorMessage(), $this->formatter->getErrorCode());
        }

        return $formatted;
    }
}
