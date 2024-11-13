<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\IntlNumber;

use DateInterval;
use IntlException;
use LastDragon_ru\LaraASP\Formatter\Formatter;
use LastDragon_ru\LaraASP\Formatter\Utils\Duration;
use NumberFormatter;
use Override;

/**
 * @see NumberFormatter
 *
 * @extends IntlFormat<?IntlOptions, DateInterval|float|int|null>
 */
class IntlDurationFormat extends IntlFormat {
    /**
     * @param list<IntlOptions|null> $options
     */
    public function __construct(Formatter $formatter, array $options = []) {
        parent::__construct($formatter, [
            new IntlOptions(NumberFormatter::DURATION),
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
