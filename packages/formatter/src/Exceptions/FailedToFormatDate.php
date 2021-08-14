<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use LastDragon_ru\LaraASP\Formatter\PackageException;
use Throwable;

use function sprintf;

class FailedToFormatDate extends PackageException {
    public function __construct(
        protected string $type,
        protected int $errorCode,
        protected string $errorMessage,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Date formatting for type `%s` failed: `%s` (`%s`).',
            $this->getType(),
            $this->getErrorMessage(),
            $this->getErrorCode(),
        ), $previous);
    }

    public function getType(): string {
        return $this->type;
    }

    public function getErrorCode(): int {
        return $this->errorCode;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }
}
