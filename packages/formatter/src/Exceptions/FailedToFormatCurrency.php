<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use Throwable;

use function sprintf;

class FailedToFormatCurrency extends FailedToFormatValue {
    public function __construct(
        protected string $currency,
        protected string $format,
        int $intlErrorCode,
        string $intlErrorMessage,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to format currency `%s` into `%s` format: `%s` (`%s`).',
                $this->getCurrency(),
                $this->getFormat(),
                $this->getIntlErrorMessage(),
                $this->getIntlErrorCode(),
            ),
            $intlErrorCode,
            $intlErrorMessage,
            $previous,
        );
    }

    public function getCurrency(): string {
        return $this->currency;
    }

    public function getFormat(): string {
        return $this->format;
    }
}
