<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

use RuntimeException;
use Throwable;

class UnresolvedValueException extends RuntimeException {
    /**
     * @var mixed
     */
    protected $value;

    public function __construct($value, $message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
}
