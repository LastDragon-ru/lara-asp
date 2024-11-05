<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use Throwable;

use function sprintf;

class FailedToCreateDateTimeFormatter extends FailedToCreateFormatter {
    public function __construct(
        protected string $format,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to create DateTime Formatter for `%s` format.',
                $this->getFormat(),
            ),
            $previous,
        );
    }

    public function getFormat(): string {
        return $this->format;
    }
}
