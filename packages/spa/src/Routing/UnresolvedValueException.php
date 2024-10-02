<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use Exception;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class UnresolvedValueException extends RuntimeException {
    protected mixed $value;

    public function __construct(mixed $value, string $message = '', int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);

        $this->value = $value;
    }

    public function getValue(): mixed {
        return $this->value;
    }

    public function getInnerException(): Exception {
        return new NotFoundHttpException($this->getMessage() !== '' ? $this->getMessage() : 'Not found.', $this);
    }
}
