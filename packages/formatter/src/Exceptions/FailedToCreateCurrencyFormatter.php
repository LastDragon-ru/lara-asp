<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use Throwable;

use function sprintf;

class FailedToCreateCurrencyFormatter extends FailedToCreateFormatter {
    public function __construct(
        protected ?string $currency,
        protected string $format,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to create Currency Formatter for `%s` currency and `%s` format.',
                $this->getCurrency(),
                $this->getFormat(),
            ),
            $previous,
        );
    }

    public function getCurrency(): ?string {
        return $this->currency;
    }

    public function getFormat(): string {
        return $this->format;
    }
}
