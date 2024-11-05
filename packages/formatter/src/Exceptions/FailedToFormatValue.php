<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use LastDragon_ru\LaraASP\Formatter\PackageException;
use Throwable;

abstract class FailedToFormatValue extends PackageException {
    public function __construct(
        string $message,
        protected int $intlErrorCode,
        protected string $intlErrorMessage,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }

    public function getIntlErrorCode(): int {
        return $this->intlErrorCode;
    }

    public function getIntlErrorMessage(): string {
        return $this->intlErrorMessage;
    }
}
