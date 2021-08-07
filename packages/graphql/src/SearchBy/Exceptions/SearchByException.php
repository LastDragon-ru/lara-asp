<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Exception;
use Throwable;

abstract class SearchByException extends Exception {
    public function __construct(string $message, Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
    }
}
