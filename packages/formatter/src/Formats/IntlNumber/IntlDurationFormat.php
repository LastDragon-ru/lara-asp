<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber;

use DateInterval;
use IntlException;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\PackageConfig;
use LastDragon_ru\LaraASP\Formatter\Utils\Duration;
use NumberFormatter;
use Override;

/**
 * @see NumberFormatter
 *
 * @extends IntlFormat<?IntlNumberOptions, DateInterval|float|int|null>
 */
readonly class IntlDurationFormat extends IntlFormat {
    /**
     * @param list<IntlNumberOptions|null> $options
     */
    public function __construct(PackageConfig $config, Formatter $formatter, array $options = []) {
        parent::__construct($config, $formatter, [
            new IntlNumberOptions(NumberFormatter::DURATION),
            ...$options,
        ]);
    }

    #[Override]
    public function __invoke(mixed $value): string {
        $value     = Duration::getTimestamp($value);
        $formatted = $this->formatter->format($value);

        if ($formatted === false) {
            throw new IntlException($this->formatter->getErrorMessage(), $this->formatter->getErrorCode());
        }

        return $formatted;
    }
}
