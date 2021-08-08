<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use IntlDateFormatter;
use LastDragon_ru\LaraASP\Formatter\PackageException;
use Throwable;

use function sprintf;

class FailedToCreateDateFormatter extends PackageException {
    public function __construct(
        protected string $type,
        protected string|int|null $format,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Failed to create instance of `%s` for type `%s` with format `%s`.',
            IntlDateFormatter::class,
            $this->getType(),
            $this->getFormat() ?? 'null',
        ), $previous);
    }

    public function getType(): string {
        return $this->type;
    }

    public function getFormat(): string|int|null {
        return $this->format;
    }
}
