<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use Throwable;

use function sprintf;

class FailedToFormatNumber extends FailedToFormatValue {
    public function __construct(
        protected string $format,
        int $intlErrorCode,
        string $intlErrorMessage,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to format number into `%s` format: `%s` (`%s`).',
                $this->getFormat(),
                $this->getIntlErrorMessage(),
                $this->getIntlErrorCode(),
            ),
            $intlErrorCode,
            $intlErrorMessage,
            $previous,
        );
    }

    public function getFormat(): string {
        return $this->format;
    }
}
