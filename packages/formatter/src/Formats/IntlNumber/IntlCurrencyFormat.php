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
 * @extends IntlFormat<?IntlNumberOptions, array{float|int|null, ?non-empty-string}|float|int|null>
 */
class IntlCurrencyFormat extends IntlFormat {
    /**
     * @param list<IntlNumberOptions|null> $options
     */
    public function __construct(PackageConfig $config, Formatter $formatter, array $options = []) {
        parent::__construct($config, $formatter, [
            new IntlNumberOptions(NumberFormatter::CURRENCY),
            ...$options,
        ]);
    }

    #[Override]
    public function __invoke(mixed $value): string {
        [$value, $currency] = is_array($value) ? $value : [$value, null];
        $value            ??= 0;
        $currency         ??= $this->formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE);
        $formatted          = $this->formatter->formatCurrency($value, $currency);

        if ($formatted === false) {
            throw new IntlException($this->formatter->getErrorMessage(), $this->formatter->getErrorCode());
        }

        return $formatted;
    }
}
