<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use LastDragon_ru\LaraASP\Formatter\PackageException;
use Throwable;

use function sprintf;

class FormatterFailedToFormatValue extends PackageException {
    public function __construct(
        protected string $format,
        protected mixed $value,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to format value into `%s` format.',
                $this->getFormat(),
            ),
            $previous,
        );
    }

    public function getFormat(): string {
        return $this->format;
    }

    public function getValue(): mixed {
        return $this->value;
    }
}
