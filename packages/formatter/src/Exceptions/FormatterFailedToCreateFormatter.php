<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use Throwable;

use function sprintf;

class FormatterFailedToCreateFormatter extends FormatterException {
    public function __construct(
        protected string $formatter,
        protected string $format,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to create `%s` formatter for `%s` format.',
                $this->getFormatter(),
                $this->getFormat(),
            ),
            $previous,
        );
    }

    public function getFormatter(): string {
        return $this->formatter;
    }

    public function getFormat(): string {
        return $this->format;
    }
}
