<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use RuntimeException;
use Throwable;

class UnresolvedValueException extends RuntimeException {
    protected mixed $value;

    public function __construct(mixed $value, string $message = '', int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);

        $this->value = $value;
    }

    public function getValue(): mixed {
        return $this->value;
    }
}
